"""Icônes animées des 20 filières (SVG au trait, 48 x 48).

Chaque icône se dessine au trait à l'apparition (classe « trace » ajoutée automatiquement, pathLength=1),
puis garde une petite animation propre à son secteur (classes a-*, définies dans dualtech.css).
Un élément marqué data-libre n'est pas tracé (il porte sa propre animation de tirets).
"""
import re


def _cube(cx, cy, d=0):
    p = f"M{cx} {cy - 7}L{cx + 6} {cy - 3.5}V{cy + 3.5}L{cx} {cy + 7}L{cx - 6} {cy + 3.5}V{cy - 3.5}Z"
    return f'<g class="a-blink" style="--d:{d}s"><path d="{p}"/><path d="M{cx - 6} {cy - 3.5}L{cx} {cy}L{cx + 6} {cy - 3.5}M{cx} {cy}V{cy + 7}"/></g>'


_NEURONES = [(10, 14), (10, 24), (10, 34), (24, 10), (24, 20), (24, 28), (24, 38), (38, 18), (38, 30)]
_LIENS = [(a, b) for a in range(3) for b in range(3, 7)] + [(a, b) for a in range(3, 7) for b in range(7, 9)]

ICONES = {
    "Aéronautique": '<g class="a-flotte"><path d="M24 6c1.6 0 2.4 1.6 2.4 3.6V19L41 27v3.4l-14.6-4.8V35l4.6 3.4V41L24 39l-7 2v-2.6l4.6-3.4v-9.4L7 30.4V27l14.6-8V9.6C21.6 7.6 22.4 6 24 6Z"/></g>'
                    '<path data-libre class="a-tirets" d="M24 44v3M17 43v3M31 43v3"/>',
    "Biotechnologies": '<path d="M16 6c0 8 16 10 16 18s-16 10-16 18"/><path d="M32 6c0 8-16 10-16 18s16 10 16 18"/>'
                       '<g class="a-barreaux"><path d="M18 10h12" style="--d:0s"/><path d="M21 17.5h6" style="--d:.2s"/><path d="M21 30.5h6" style="--d:.4s"/><path d="M18 38h12" style="--d:.6s"/></g>',
    "Blockchain": _cube(24, 11, 0) + _cube(12, 32, .5) + _cube(36, 32, 1) + '<path d="M20 17l-4 8M28 17l4 8M19 34h10"/>',
    "Communication": '<circle cx="24" cy="19" r="2.2"/><path d="M24 21.5L18 42M24 21.5L30 42M20.2 34h7.6"/>'
                     '<path class="a-onde" style="--d:0s" d="M30 13a9 9 0 0 1 0 12M18 13a9 9 0 0 0 0 12"/>'
                     '<path class="a-onde" style="--d:.35s" d="M34.5 8.5a15.5 15.5 0 0 1 0 21M13.5 8.5a15.5 15.5 0 0 0 0 21"/>',
    "Construction & infrastructures": '<path d="M4 30h40M8 30Q24 8 40 30M14 23.3V30M19 20.1V30M24 19V30M29 20.1V30M34 23.3V30M10 30v12M38 30v12"/>'
                                      '<path data-libre class="a-trafic" d="M6 34h36"/>',
    "Cybersécurité": '<path d="M24 5l15 5v12c0 10-7 17-15 21C16 39 9 32 9 22V10Z"/><rect x="18" y="21" width="12" height="9" rx="1.5"/><path d="M20.5 21v-3a3.5 3.5 0 0 1 7 0v3"/>'
                     '<path data-libre class="a-scan" d="M12 12h24"/>',
    "Data": '<path d="M8 40h32M8 40V8"/><g class="a-barres"><rect x="13" y="28" width="5" height="12" style="--d:0s"/><rect x="21" y="20" width="5" height="20" style="--d:.15s"/><rect x="29" y="24" width="5" height="16" style="--d:.3s"/></g>'
            '<path d="M12 21l8-7 8 4 10-9"/><circle class="a-pulse" cx="38" cy="9" r="1.8"/>',
    "Électronique": '<rect x="15" y="15" width="18" height="18" rx="2"/><rect x="20" y="20" width="8" height="8" rx="1"/>'
                    '<path d="M19 15V8M24 15V8M29 15V8M19 33v7M24 33v7M29 33v7M15 19H8M15 24H8M15 29H8M33 19h7M33 24h7M33 29h7"/>'
                    '<path data-libre class="a-courant" d="M19 8V15M29 40V33M8 24H15M40 19H33"/>',
    "Énergie": '<path class="a-eclair" d="M27 5L13 27h10l-3 16 15-22H25Z"/><circle class="a-etincelle" style="--d:0s" cx="38" cy="10" r="1.2"/><circle class="a-etincelle" style="--d:.6s" cx="10" cy="38" r="1.2"/>',
    "Greentech": '<g class="a-balance"><path d="M10 38C10 18 22 8 40 8c0 18-10 30-30 30Z"/><path d="M10 38l20-20M18 30h7M23 25v-7M28 20h5"/></g>',
    "Infrastructures critiques": '<circle cx="24" cy="24" r="16"/><circle cx="24" cy="24" r="10"/><circle cx="24" cy="24" r="3"/><path d="M24 6v4M24 38v4M6 24h4M38 24h4"/>'
                                 '<path data-libre class="a-radar" d="M24 24V8a16 16 0 0 1 13.9 8Z"/><circle class="a-pulse" cx="31" cy="15" r="1.4"/>',
    "Intelligence artificielle": "".join(f'<path d="M{_NEURONES[a][0]} {_NEURONES[a][1]}L{_NEURONES[b][0]} {_NEURONES[b][1]}"/>' for a, b in _LIENS)
                                 + "".join(f'<circle class="a-neurone" style="--d:{n * .18:.2f}s" cx="{x}" cy="{y}" r="2.4"/>' for n, (x, y) in enumerate(_NEURONES)),
    "Maritime": '<g class="a-roulis"><path d="M8 28h32l-5 9H13Z"/><path d="M16 28v-6h14v6M21 22v-5h6v5M26 17v-6"/></g>'
                '<path data-libre class="a-houle" d="M-4 42q4-3 8 0t8 0 8 0 8 0 8 0 8 0 8 0"/>',
    "Matières premières critiques": '<path d="M14 8h20l8 10-18 24L6 18Z"/><path d="M6 18h36M20 18l4-10 4 10M20 18l4 24 4-24"/>'
                                    '<path class="a-scintille" d="M40 4v6M37 7h6"/>',
    "Médical": '<rect x="5" y="9" width="38" height="30" rx="3"/><path data-libre class="a-ecg" d="M7 26h8l3-7 4 14 4-19 4 12h11"/>',
    "Mobilité": '<path d="M6 31v-7l6-8h18l8 8h4v7"/><path d="M18 31h12M6 31h2M40 31h2M14 16v8h22"/>'
                '<g class="a-roue"><circle cx="13" cy="32" r="4"/><path d="M13 28v8M9 32h8"/></g><g class="a-roue"><circle cx="35" cy="32" r="4"/><path d="M35 28v8M31 32h8"/></g>'
                '<path data-libre class="a-route" d="M2 42h44"/>',
    "Photonique & optronique": '<path d="M24 10l12 24H12Z"/><path data-libre class="a-faisceau" d="M2 25l15-2M29.5 22L46 15M30.5 24.5H46M31 27l15 7"/>',
    "Quantique": '<circle cx="24" cy="24" r="2.6"/><g class="a-orbite"><ellipse cx="24" cy="24" rx="18" ry="7"/><ellipse cx="24" cy="24" rx="18" ry="7" transform="rotate(60 24 24)"/><ellipse cx="24" cy="24" rx="18" ry="7" transform="rotate(120 24 24)"/><circle cx="42" cy="24" r="1.6"/></g>',
    "Robotique": '<path d="M10 42h16M13 42v-4h10v4"/><circle cx="18" cy="28" r="2.4"/><path d="M18 38V30.4"/>'
                 '<g class="a-bras"><path d="M19.8 26.4L28.4 19.6"/><circle cx="30" cy="18.4" r="2.4"/><path d="M32 19.8l6 4.4M38 24.2l4-1.8M38 24.2l2.6 3.4"/></g>',
    "Spatial": '<g class="a-satellite"><rect x="20" y="20" width="8" height="8" rx="1"/><rect x="5" y="21" width="12" height="6"/><rect x="31" y="21" width="12" height="6"/><path d="M9 21v6M13 21v6M35 21v6M39 21v6M17 24h3M28 24h3M24 20v-5"/><circle cx="24" cy="13.5" r="1.5"/></g>'
               '<path data-libre class="a-orbite-tirets" d="M4 36c8 6 32 6 40 0"/>',
}


def icone(nom, cls="secteur__ico"):
    svg = ICONES[nom]
    # chaque forme sans data-libre est tracée à l'apparition
    svg = re.sub(r"<(path|circle|rect|ellipse)(?![^>]*data-libre)", r'<\1 pathLength="1" class-trace', svg)
    svg = re.sub(r'class-trace([^>]*?) class="([^"]+)"', r'\1 class="trace \2"', svg)
    svg = svg.replace("class-trace", 'class="trace"').replace(" data-libre", "")
    return f'<svg class="{cls}" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">{svg}</svg>'


if __name__ == "__main__":
    print(icone("Cybersécurité"))
