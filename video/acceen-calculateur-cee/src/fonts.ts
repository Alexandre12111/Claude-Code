import { continueRender, delayRender, staticFile } from "remotion";

// Polices du site acceen.com (fichiers woff2 servis par le site).
const FONTS: { family: string; file: string; weight: string }[] = [
  { family: "Baloo 2", file: "fonts/Baloo2-Bold.woff2", weight: "700" },
  { family: "Fustat", file: "fonts/Fustat-Regular.woff2", weight: "400" },
  { family: "Fustat", file: "fonts/Fustat-Bold.woff2", weight: "700" },
  { family: "Montserrat", file: "fonts/Montserrat-Regular.woff2", weight: "400" },
];

let loaded = false;
export const loadFonts = () => {
  if (loaded || typeof document === "undefined") return;
  loaded = true;
  const handle = delayRender("Chargement des polices");
  Promise.all(
    FONTS.map((f) => {
      const face = new FontFace(f.family, `url(${staticFile(f.file)}) format("woff2")`, {
        weight: f.weight,
        style: "normal",
      });
      return face.load().then((ff) => document.fonts.add(ff));
    })
  )
    .then(() => continueRender(handle))
    .catch((e) => {
      console.error(e);
      continueRender(handle);
    });
};
