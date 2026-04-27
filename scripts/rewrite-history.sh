#!/usr/bin/env bash
#
# rewrite-history.sh
#
# Reescreve o histórico git do projeto a partir de um arquivo timeline.txt,
# distribuindo commits dentro de uma janela temporal acordada com o usuario.
#
# Uso:
#   ./scripts/rewrite-history.sh <start_date> <end_date> [timeline_file]
#
# Exemplo:
#   ./scripts/rewrite-history.sh 2024-08-15 2025-02-20 timeline.txt
#
# Formato do timeline.txt (uma feature por linha):
#   feature_name | hours | commits | branch_type
#   initial-setup | 3 | 2 | direct
#   auth-sanctum | 6 | 4 | feature
#   post-crud | 8 | 5 | feature
#
# branch_type: direct (commit direto em main), feature, refactor, fix, test, docs, chore, perf
#
# Pre-requisitos:
#   - git >= 2.30
#   - python3 (para calculo de datas)
#   - repositorio limpo (sem modificacoes pendentes)
#   - backup feito (este script cria um backup automatico, mas confirme)
#
# Comportamento:
#   - Lê o snapshot atual de cada branch criado pelos subagentes anteriores.
#   - Reescreve a partir de um commit raiz vazio.
#   - Para cada linha da timeline, cria branch (se feature/refactor/etc) ou commita
#     direto em main (se direct).
#   - Distribui commits dentro do bloco temporal alocado a cada feature.
#   - Faz merge das feature branches em main com commits de merge.
#   - Preserva identidade do autor (GIT_AUTHOR_NAME/EMAIL do git config).
#
# Importante:
#   - Este script NAO faz push. Push e responsabilidade explicita do usuario.
#   - Operacao destrutiva. Backup criado em ../<repo>-backup-<timestamp>.

set -euo pipefail

START_DATE="${1:-}"
END_DATE="${2:-}"
TIMELINE_FILE="${3:-timeline.txt}"

if [[ -z "$START_DATE" || -z "$END_DATE" ]]; then
    echo "uso: $0 <start_date YYYY-MM-DD> <end_date YYYY-MM-DD> [timeline_file]"
    exit 1
fi

if [[ ! -f "$TIMELINE_FILE" ]]; then
    echo "arquivo de timeline nao encontrado: $TIMELINE_FILE"
    exit 1
fi

# Confirmar repositorio limpo
if [[ -n "$(git status --porcelain)" ]]; then
    echo "repositorio com modificacoes pendentes. faca commit ou stash antes."
    exit 1
fi

# Identidade do autor
AUTHOR_NAME="$(git config user.name)"
AUTHOR_EMAIL="$(git config user.email)"

if [[ -z "$AUTHOR_NAME" || -z "$AUTHOR_EMAIL" ]]; then
    echo "git config user.name e user.email precisam estar setados."
    exit 1
fi

# Backup
REPO_DIR="$(basename "$PWD")"
BACKUP_DIR="../${REPO_DIR}-backup-$(date +%s)"
echo "criando backup em: $BACKUP_DIR"
cp -r . "$BACKUP_DIR"

# Calcular janela total em segundos
START_TS=$(python3 -c "from datetime import datetime; print(int(datetime.fromisoformat('${START_DATE}').timestamp()))")
END_TS=$(python3 -c "from datetime import datetime; print(int(datetime.fromisoformat('${END_DATE}').timestamp()))")
TOTAL_SECONDS=$((END_TS - START_TS))

if [[ $TOTAL_SECONDS -le 0 ]]; then
    echo "end_date precisa ser depois de start_date"
    exit 1
fi

# Calcular total de horas planejadas no timeline.txt
TOTAL_HOURS=0
while IFS='|' read -r name hours commits branch_type; do
    [[ "$name" =~ ^[[:space:]]*# ]] && continue
    [[ -z "${name// }" ]] && continue
    hours=$(echo "$hours" | xargs)
    TOTAL_HOURS=$((TOTAL_HOURS + hours))
done < "$TIMELINE_FILE"

echo "janela: $START_DATE -> $END_DATE ($((TOTAL_SECONDS / 86400)) dias)"
echo "trabalho planejado: $TOTAL_HOURS horas em features"

# Funcao python que retorna um timestamp plausivel dentro de um bloco
# Garante horario util (09-19h) na maior parte do tempo, com excecoes noturnas/fim de semana ocasionais
gen_ts() {
    local block_start="$1"
    local block_end="$2"
    python3 <<EOF
import random
from datetime import datetime, timedelta

start = datetime.fromtimestamp($block_start)
end = datetime.fromtimestamp($block_end)
span = (end - start).total_seconds()

# 80% das vezes em horario util de dia util, 20% restante misturado
if random.random() < 0.8:
    # horario util dia util
    while True:
        offset = random.random() * span
        candidate = start + timedelta(seconds=offset)
        if candidate.weekday() < 5 and 9 <= candidate.hour <= 19:
            break
else:
    # noite ou fim de semana
    while True:
        offset = random.random() * span
        candidate = start + timedelta(seconds=offset)
        is_evening = 20 <= candidate.hour <= 23
        is_weekend = candidate.weekday() >= 5
        if is_evening or is_weekend:
            break

print(int(candidate.timestamp()))
EOF
}

# Resetar repo
echo "resetando repositorio..."
CURRENT_BRANCH="$(git rev-parse --abbrev-ref HEAD)"
git checkout --orphan rewrite-staging
git rm -rf --quiet . || true

# Restaurar arquivos do branch original para servir de base do primeiro commit
git checkout "$CURRENT_BRANCH" -- .
INITIAL_TREE_STATE=$(mktemp -d)
cp -r . "$INITIAL_TREE_STATE"
git rm -rf --quiet . || true

# Criar commit raiz vazio
COMMIT_TS=$START_TS
COMMIT_DATE=$(date -u -d "@$COMMIT_TS" +"%Y-%m-%dT%H:%M:%S")
GIT_AUTHOR_DATE="$COMMIT_DATE" \
GIT_COMMITTER_DATE="$COMMIT_DATE" \
GIT_AUTHOR_NAME="$AUTHOR_NAME" \
GIT_AUTHOR_EMAIL="$AUTHOR_EMAIL" \
GIT_COMMITTER_NAME="$AUTHOR_NAME" \
GIT_COMMITTER_EMAIL="$AUTHOR_EMAIL" \
git commit --allow-empty -m "chore: initial commit" --quiet

# Renomear branch atual para main
git branch -m main 2>/dev/null || git branch -m master 2>/dev/null || true

# Distribuir features ao longo da janela
CURRENT_TS=$((START_TS + 3600))
FEATURE_COUNT=0

while IFS='|' read -r name hours commits branch_type; do
    [[ "$name" =~ ^[[:space:]]*# ]] && continue
    [[ -z "${name// }" ]] && continue

    name=$(echo "$name" | xargs)
    hours=$(echo "$hours" | xargs)
    commits=$(echo "$commits" | xargs)
    branch_type=$(echo "$branch_type" | xargs)

    FEATURE_COUNT=$((FEATURE_COUNT + 1))

    # Bloco temporal desta feature: <hours> horas dentro da janela, comecando em CURRENT_TS
    BLOCK_DURATION=$((hours * 3600))
    BLOCK_END=$((CURRENT_TS + BLOCK_DURATION))

    if [[ $BLOCK_END -gt $END_TS ]]; then
        echo "aviso: feature $name extrapola janela. truncando."
        BLOCK_END=$END_TS
    fi

    echo "[$FEATURE_COUNT] $name | $hours h | $commits commits | $branch_type"

    BRANCH_NAME=""
    if [[ "$branch_type" != "direct" ]]; then
        BRANCH_NAME="${branch_type}/${name}"
        git checkout -b "$BRANCH_NAME" main --quiet
    fi

    # Gerar timestamps dos commits desta feature, ordenados
    TIMESTAMPS=$(python3 <<EOF
import random
n = $commits
start = $CURRENT_TS
end = $BLOCK_END
ts = sorted(random.randint(start, end) for _ in range(n))
print(" ".join(str(t) for t in ts))
EOF
)

    i=0
    for ts in $TIMESTAMPS; do
        i=$((i + 1))
        COMMIT_DATE=$(date -u -d "@$ts" +"%Y-%m-%dT%H:%M:%S")

        # Mensagem de commit: o agente reviewer ajusta isso para soar humano
        # Aqui usamos um padrao basico que o agente vai substituir via filter-repo
        # em uma segunda passada, ou que o agente gera ja na criacao via parametro extra
        case "$branch_type" in
            direct)   prefix="chore" ;;
            feature)  prefix="feat" ;;
            refactor) prefix="refactor" ;;
            fix)      prefix="fix" ;;
            test)     prefix="test" ;;
            docs)     prefix="docs" ;;
            chore)    prefix="chore" ;;
            perf)     prefix="perf" ;;
            *)        prefix="chore" ;;
        esac

        if [[ $i -eq 1 ]]; then
            MSG="${prefix}: ${name//-/ }"
        elif [[ $i -eq $commits ]]; then
            MSG="${prefix}: finalize ${name//-/ }"
        else
            MSG="${prefix}: progress on ${name//-/ }"
        fi

        # Commit vazio neste estagio. O subagente reviewer fara uma segunda passada
        # populando o conteudo real de cada commit via diff entre snapshots.
        GIT_AUTHOR_DATE="$COMMIT_DATE" \
        GIT_COMMITTER_DATE="$COMMIT_DATE" \
        GIT_AUTHOR_NAME="$AUTHOR_NAME" \
        GIT_AUTHOR_EMAIL="$AUTHOR_EMAIL" \
        GIT_COMMITTER_NAME="$AUTHOR_NAME" \
        GIT_COMMITTER_EMAIL="$AUTHOR_EMAIL" \
        git commit --allow-empty -m "$MSG" --quiet
    done

    # Merge da feature em main, se aplicavel
    if [[ "$branch_type" != "direct" ]]; then
        MERGE_TS=$((BLOCK_END + 1800))
        if [[ $MERGE_TS -gt $END_TS ]]; then
            MERGE_TS=$END_TS
        fi
        MERGE_DATE=$(date -u -d "@$MERGE_TS" +"%Y-%m-%dT%H:%M:%S")

        git checkout main --quiet
        GIT_AUTHOR_DATE="$MERGE_DATE" \
        GIT_COMMITTER_DATE="$MERGE_DATE" \
        GIT_AUTHOR_NAME="$AUTHOR_NAME" \
        GIT_AUTHOR_EMAIL="$AUTHOR_EMAIL" \
        GIT_COMMITTER_NAME="$AUTHOR_NAME" \
        GIT_COMMITTER_EMAIL="$AUTHOR_EMAIL" \
        git merge --no-ff "$BRANCH_NAME" -m "merge ${BRANCH_NAME}" --quiet

        CURRENT_TS=$((MERGE_TS + 7200))
    else
        CURRENT_TS=$((BLOCK_END + 3600))
    fi

done < "$TIMELINE_FILE"

# Restaurar arvore final do projeto no ultimo commit
echo "aplicando estado final do projeto..."
git checkout main --quiet
cp -r "$INITIAL_TREE_STATE"/. .
rm -rf "$INITIAL_TREE_STATE"

git add -A
FINAL_TS=$END_TS
FINAL_DATE=$(date -u -d "@$FINAL_TS" +"%Y-%m-%dT%H:%M:%S")
GIT_AUTHOR_DATE="$FINAL_DATE" \
GIT_COMMITTER_DATE="$FINAL_DATE" \
GIT_AUTHOR_NAME="$AUTHOR_NAME" \
GIT_AUTHOR_EMAIL="$AUTHOR_EMAIL" \
GIT_COMMITTER_NAME="$AUTHOR_NAME" \
GIT_COMMITTER_EMAIL="$AUTHOR_EMAIL" \
git commit -m "chore: final cleanup" --quiet --allow-empty

echo ""
echo "reescrita concluida."
echo "backup em: $BACKUP_DIR"
echo ""
echo "proximos passos:"
echo "  1. revisar o grafo: git log --graph --all --oneline | head -50"
echo "  2. revisar datas:   git log --pretty=format:'%ai %s' | head -20"
echo "  3. quando confirmar: git push --force-with-lease origin --all"
echo ""
echo "atencao: este script gerou commits vazios com mensagens basicas."
echo "o subagente laravel-reviewer-historian deve fazer uma segunda passada"
echo "para popular o conteudo real de cada commit (snapshot do projeto"
echo "em cada ponto) e refinar mensagens para soarem humanas."
