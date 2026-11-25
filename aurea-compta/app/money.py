"""Montants : toujours en centimes entiers (int). Aucun float dans les calculs.

Les taux sont des Decimal (ex. Decimal("20") pour 20 %).
Arrondi commercial : demi à l'euro supérieur (ROUND_HALF_UP), au centime.
"""
from decimal import Decimal, ROUND_HALF_UP, InvalidOperation

CENT = Decimal("1")


def to_cents(value) -> int:
    """Convertit '12,34', '12.34', 12.34 (str de préférence) ou Decimal en centimes."""
    if value is None or value == "":
        return 0
    if isinstance(value, int) and not isinstance(value, bool):
        return value * 100
    s = str(value).strip().replace(" ", "").replace(" ", "").replace("€", "")
    if "," in s and "." in s:
        s = s.replace(".", "").replace(",", ".")
    else:
        s = s.replace(",", ".")
    try:
        d = Decimal(s)
    except InvalidOperation as exc:
        raise ValueError(f"Montant invalide : {value!r}") from exc
    return int((d * 100).quantize(CENT, rounding=ROUND_HALF_UP))


def fmt(cents: int) -> str:
    """Format français : 1 234,56 €"""
    neg = cents < 0
    c = abs(int(cents))
    euros, cts = divmod(c, 100)
    s = f"{euros:,}".replace(",", " ") + f",{cts:02d} €"
    return ("-" if neg else "") + s


def plain(cents: int) -> str:
    """Format numérique pour exports : 1234,56 (virgule décimale, FEC)."""
    neg = cents < 0
    euros, cts = divmod(abs(int(cents)), 100)
    return ("-" if neg else "") + f"{euros},{cts:02d}"


def rate(value) -> Decimal:
    return Decimal(str(value).replace(",", ".")) if value not in (None, "") else Decimal("0")


def pct_of(cents: int, pct) -> int:
    """cents * pct / 100 arrondi au centime."""
    return int((Decimal(cents) * rate(pct) / Decimal(100)).quantize(CENT, rounding=ROUND_HALF_UP))


def vat_from_ht(ht: int, vat_rate) -> int:
    return pct_of(ht, vat_rate)


def split_ttc(ttc: int, vat_rate) -> tuple[int, int]:
    """Décompose un TTC en (HT, TVA). TVA = TTC - HT pour garantir HT + TVA = TTC."""
    r = rate(vat_rate)
    if r == 0:
        return ttc, 0
    ht = int((Decimal(ttc) * 100 / (100 + r)).quantize(CENT, rounding=ROUND_HALF_UP))
    return ht, ttc - ht


def prorata(total: int, part: int, whole: int) -> int:
    """total * part / whole arrondi au centime (whole != 0)."""
    if whole == 0:
        return 0
    return int((Decimal(total) * Decimal(part) / Decimal(whole)).quantize(CENT, rounding=ROUND_HALF_UP))
