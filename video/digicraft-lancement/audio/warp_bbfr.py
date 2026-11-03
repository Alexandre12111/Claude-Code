"""Table de ralenti de la version française allongée (même table que brand_yc/bbfr/warp.js).
F[j] : facteur de la mesure j du film d'origine (2 = deux fois plus lente, 0 = mesure coupée : tablette)."""
F = [1, 2, 2, 1, 1, 1, 2, 1, 1, 2, 2, 0, 1, 1, 1, 2, 1, 1, 1, 1, 1, 1]
OFF, BAR, END0 = 0.7, 2.5, 54.0
B = [OFF]
for j, f in enumerate(F):
    B.append(B[j] + BAR * f)


def fwd(t):
    """Temps du film d'origine -> temps de la version allongée."""
    if t <= OFF:
        return t
    j = int((t - OFF) // BAR)
    if j < len(F):
        return B[j] + (t - OFF - BAR * j) * F[j]
    return B[-1] + (t - OFF - BAR * len(F))


END = fwd(END0)
NEWBAR = [sum(F[:j]) for j in range(len(F) + 1)]  # index de mesure dans la version allongée


def kept(t):
    """Faux si l'instant t du film d'origine tombe dans une mesure coupée (F = 0)."""
    j = int((t - OFF) // BAR)
    return not (t > OFF and j < len(F) and F[j] == 0)
