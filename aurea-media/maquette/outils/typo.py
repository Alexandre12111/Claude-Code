"""Espaces insécables de la typographie française (avant ? ! : ; et à l'intérieur des guillemets), dans le texte seulement."""
import re, sys
from pathlib import Path

def corriger(html):
    morceaux = re.split(r'(<script.*?</script>|<style.*?</style>|<[^>]+>)', html, flags=re.S)
    for i, m in enumerate(morceaux):
        if m.startswith('<'):
            continue
        m = re.sub(r' ([?!:;»])', ' \\1', m)
        m = m.replace('« ', '« ')
        morceaux[i] = m
    return ''.join(morceaux)

for f in sys.argv[1:]:
    p = Path(f); p.write_text(corriger(p.read_text()))
