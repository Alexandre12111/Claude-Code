"""Table de retiming V4 : segments de la timeline V3 et leur nouvelle durée."""
SEGMENTS = [
    # (début V3, fin V3, nouvelle durée, commentaire)
    (0.0, 2.3, 2.3, 'logo'),
    (2.3, 3.0, 0.25, 'logo, pause raccourcie'),
    (3.0, 3.6, 0.6, 'plongée dans le logo'),
    (3.6, 6.7, 3.1, 'phrase VALEUR'),
    (6.7, 8.3, 2.3, 'lecture VALEUR'),
    (8.3, 10.3, 2.0, 'morph PRODUCTIVITÉ'),
    (10.3, 11.1, 0.15, 'sous-titre coupé'),
    (11.1, 12.95, 1.15, 'blocs accélérés'),
    (12.95, 13.2, 0.9, 'lecture PRODUCTIVITÉ'),
    (13.2, 15.4, 2.2, 'idées DG DAF DRH'),
    (15.4, 16.9, 1.9, 'lecture des cas d usage'),
    (16.9, 17.6, 0.7, 'titre outil'),
    (17.6, 19.5, 1.5, 'file d attente accélérée'),
    (19.5, 20.45, 0.95, 'déblocage DigiCraft'),
    (20.45, 21.7, 1.25, 'Découvrez la solution'),
    (21.7, 21.95, 1.0, 'lecture Découvrez'),
    (21.95, 23.0, 1.05, 'Comment ça marche'),
    (23.0, 23.1, 0.6, 'lecture Comment ça marche'),
    (23.1, 24.15, 1.05, 'zoom sur le prompt'),
    (24.15, 26.1, 2.2, 'frappe du prompt'),
    (26.1, 26.9, 1.4, 'lecture du prompt'),
    (26.9, 31.3, 3.9, 'construction accélérée'),
    (31.3, 33.7, 2.4, 'publication et mobile'),
    (33.7, 34.6, 1.7, 'lecture En ligne'),
    (34.6, 36.0, 1.4, 'transition chiffres'),
    (36.0, 40.25, 4.5, 'pop up chiffres'),
    (40.25, 40.3, 1.5, 'lecture des chiffres'),
    (40.3, 42.6, 2.6, 'sérénité apparition'),
    (42.6, 44.2, 3.3, 'lecture sérénité'),
    (44.2, 45.9, 1.7, 'Créé par vous'),
    (45.9, 46.05, 0.75, 'lecture Créé par vous'),
    (46.05, 49.4, 3.35, 'plan final'),
    (49.4, 51.0, 1.4, 'fin'),
]


def table():
    pts = [(0.0, 0.0)]
    n = 0.0
    for a, b, d, _ in SEGMENTS:
        assert abs(pts[-1][1] - a) < 1e-9, (a, pts[-1])
        n += d
        pts.append((round(n, 4), b))
    return pts


def to_new(t3):
    pts = table()
    for (n0, v0), (n1, v1) in zip(pts, pts[1:]):
        if t3 <= v1:
            return n0 + (t3 - v0) / (v1 - v0) * (n1 - n0) if v1 > v0 else n0
    return pts[-1][0] + (t3 - pts[-1][1])


if __name__ == '__main__':
    pts = table()
    print('durée', pts[-1][0])
    for name, t in [('zoom logo', 3.0), ('VALEUR ligne A', 4.1), ('sortie VALEUR', 8.3), ('PRODUCTIVITE', 9.2), ('slash idées', 13.2), ('titre outil', 17.0), ('déblocage', 19.5), ('Découvrez', 20.4), ('Comment', 22.2), ('frappe', 23.9), ('clic Créer', 26.9), ('build', 27.45), ('publier', 32.1), ('En ligne', 33.0), ('iris chiffres', 34.8), ('pop1', 36.0), ('pop2', 37.4), ('pop3', 38.8), ('slash sérénité', 40.3), ('slash créé', 44.3), ('fin', 46.1)]:
        print(f'{name:16s} V3 {t:6.2f} -> V4 {to_new(t):6.2f}')
