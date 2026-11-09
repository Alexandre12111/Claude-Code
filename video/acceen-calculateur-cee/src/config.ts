// ============================================================
//  Réglages de la vidéo : textes, montant et timing.
//  Toutes les valeurs de temps sont en images (30 images = 1 s).
// ============================================================

export const FPS = 30;
export const WIDTH = 1080;
export const HEIGHT = 1350;
export const DURATION = 20 * FPS; // 600 images

// ---------- Charte Acceen (slides) ----------
export const BRAND = {
  white: "#FAFAFA",
  violet: "#391267",
  lavender: "#AEA5D9",
  grey: "#C1BACB",
  yellow: "#F9DA72",
  gold: "#E5B600",
};

// ---------- Textes ----------
export const TEXTS = {
  hookKicker: "Calculateur CEE",
  hookLine1: "Estimez vos CEE",
  hookLine2Before: "en ",
  hookHighlight: "2 minutes",
  ctaTitle: "Testez le calculateur",
  ctaUrl: "acceen.com/calculateur-cee",
};

// Sous-titres (6 mots maximum). `num` s'affiche en jaune.
export const SUBTITLES: { from: number; to: number; num?: string; text: string }[] = [
  { from: 60, to: 130, num: "1", text: "Type de projet" },
  { from: 130, to: 184, num: "2", text: "Type de véhicule" },
  { from: 184, to: 238, num: "3", text: "Site d'assemblage" },
  { from: 238, to: 292, num: "4", text: "Capacité de la batterie" },
  { from: 292, to: 348, num: "5", text: "Nombre de véhicules" },
  { from: 348, to: 438, text: "Vos coordonnées, en accéléré" },
  { from: 442, to: 540, num: "5", text: "questions, un résultat immédiat" },
];

// ---------- Profil factice ----------
export const PROFILE = {
  firstName: "Camille",
  lastName: "Martin",
  email: "camille.martin@exemple.fr",
  phone: "06 00 00 00 00",
  organisation: "Société Exemple",
  job: "Responsable mobilité",
};

// ---------- Résultat ----------
export const RESULT_VALUE = 105777;

// ---------- Découpage des scènes ----------
export const SCENES = {
  hook: { from: 0, to: 60 },
  calc: { from: 58, to: 438 },
  result: { from: 438, to: 540 },
  cta: { from: 540, to: 600 },
};

// ---------- Interface du calculateur ----------
// Phases d'affichage des panneaux (catégorie > fiche > formulaire).
export const PANELS = {
  categoryOut: 94, // fin du panneau "Quel type de projet..."
  ficheOut: 126, // fin du panneau "Transport" (choix de la fiche)
  fade: 6,
};

// Clics (image du clic) et cible visée par le curseur.
export const CLICKS = {
  category: 86,
  fiche: 120,
  type: 174,
  lieu: 228,
  capacite: 282,
  nombre: 318,
  checkbox: 422,
  submit: 431,
};

// Saisie du nombre de véhicules
export const NOMBRE_VALUE = "3";
export const NOMBRE_TYPED = 325;

// Réponses validées : chaque valeur fait avancer la barre de progression.
export const ANSWERS = [CLICKS.fiche, CLICKS.type, CLICKS.lieu, CLICKS.capacite, NOMBRE_TYPED];

// Saisie accélérée des coordonnées : [début, fin]
export const TYPING: Record<keyof typeof PROFILE, [number, number]> = {
  firstName: [356, 363],
  lastName: [365, 371],
  email: [373, 385],
  phone: [387, 394],
  organisation: [396, 404],
  job: [406, 415],
};
