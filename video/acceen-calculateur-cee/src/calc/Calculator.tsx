import React from "react";
import { staticFile } from "remotion";
import "./calc.css";

// Reproduction des écrans du calculateur CEE de acceen.com.
// Les libellés sont repris mot pour mot de la configuration du site.

export type Reg = (key: string) => (el: HTMLElement | null) => void;

const CATEGORIES = [
  { slug: "transport", title: "Transport", icon: "icon-bus.svg" },
  { slug: "raccordement", title: "Raccordement à un réseau de chaleur", icon: "icon-network.svg" },
  { slug: "chauffage", title: "Système de chauffage", icon: "icon-flame.svg" },
];

const FICHES = [
  { slug: "bus", code: "01", title: "Autobus et Autocar" },
  { slug: "lourds", code: "02", title: "Véhicules lourds" },
];

export const FIELDS = [
  {
    key: "type",
    label: "Quel est le type de véhicule que vous souhaitez acquérir ?*",
    compact: false,
    options: [
      { value: "agglo_plus", label: "Autobus desservant une agglomération de plus de 250 000 habitants" },
      { value: "agglo_moins", label: "Autobus desservant une agglomération de moins de 250 000 habitants" },
      { value: "autocar", label: "Autocar" },
    ],
  },
  {
    key: "lieu",
    label: "Où se situe le site d'assemblage du véhicule ?*",
    compact: true,
    options: [
      { value: "eee", label: "Au sein de l'EEE" },
      { value: "hors", label: "Hors de l'EEE" },
    ],
  },
  {
    key: "capacite",
    label: "Quelle est la capacité du véhicule ?*",
    compact: true,
    options: [
      { value: "standard", label: "Batterie standard" },
      { value: "grande", label: 'Batterie grande capacité (ou "pantographe")' },
    ],
  },
];

export const CategoryPanel: React.FC<{ reg: Reg; hover?: string }> = ({ reg, hover }) => (
  <div className="acceen-calc__panel acceen-calc__panel--alone" ref={reg("panel-category")}>
    <h2 className="acceen-calc__title">Quel type de projet souhaitez-vous estimer&nbsp;?</h2>
    <p className="acceen-calc__sub">Choisissez la catégorie correspondant à votre projet.</p>
    <div className="acceen-calc__grid acceen-calc__grid--cat">
      {CATEGORIES.map((c) => (
        <div
          key={c.slug}
          ref={reg("cat-" + c.slug)}
          className={"acceen-calc__card" + (hover === c.slug ? " is-hover" : "")}
        >
          <span className="acceen-calc__code">
            <img src={staticFile(c.icon)} alt="" />
          </span>
          <span>{c.title}</span>
        </div>
      ))}
    </div>
  </div>
);

export const FichePanel: React.FC<{ reg: Reg; hover?: string }> = ({ reg, hover }) => (
  <div className="acceen-calc__panel acceen-calc__panel--alone" ref={reg("panel-fiche")}>
    <div className="acceen-calc__header">
      <div>
        <h2 className="acceen-calc__title">Transport</h2>
      </div>
      <div className="acceen-calc__ghost">Changer de catégorie</div>
    </div>
    <p className="acceen-calc__sub">
      Choisissez le type de travaux ou d'équipement concerné pour démarrer l'estimation.
    </p>
    <div className="acceen-calc__grid">
      {FICHES.map((f) => (
        <div
          key={f.slug}
          ref={reg("fiche-" + f.slug)}
          className={"acceen-calc__card" + (hover === f.slug ? " is-hover" : "")}
        >
          <span className="acceen-calc__code">{f.code}</span>
          <span>{f.title}</span>
        </div>
      ))}
    </div>
  </div>
);

const Typed: React.FC<{ text: string; caret: boolean }> = ({ text, caret }) => (
  <>
    {text}
    {caret ? <span className="acceen-calc__caret" /> : null}
  </>
);

export type FormState = {
  checked: Record<string, string | undefined>;
  hoverOption?: string; // "type-agglo_plus"
  nombre: string;
  focus?: string; // clé du champ en cours de saisie
  caretOn: boolean;
  contact: Record<string, string>;
  accepted: boolean;
  submitEnabled: boolean;
  submitHover: boolean;
};

export const FormPanel: React.FC<{ reg: Reg; s: FormState }> = ({ reg, s }) => {
  const input = (key: string, value: string, extra = "") => (
    <div
      ref={reg("input-" + key)}
      className={"acceen-calc__input" + (s.focus === key ? " is-focus" : "") + extra}
    >
      <Typed text={value} caret={s.focus === key && s.caretOn} />
    </div>
  );
  const contactField = (key: string, label: string, col2 = false) => (
    <p className={col2 ? "col-2" : undefined} ref={reg("field-" + key)}>
      <label>{label}</label>
      {input(key, s.contact[key] ?? "")}
    </p>
  );

  return (
    <div>
      <div className="acceen-calc__panel" ref={reg("panel-form")}>
        <div className="acceen-calc__header">
          <div>
            <div className="acceen-calc__code">01</div>
            <h2 className="acceen-calc__title">Autobus et Autocar</h2>
          </div>
          <div className="acceen-calc__ghost">Changer de fiche</div>
        </div>
        <div className="acceen-calc__fields">
          {FIELDS.map((f) => (
            <div key={f.key} ref={reg("field-" + f.key)}>
              <div className="acceen-calc__fieldset">
                <div className="acceen-calc__label">{f.label}</div>
                <div className={"acceen-calc__options" + (f.compact ? " acceen-calc__options--compact" : "")}>
                  {f.options.map((o) => {
                    const id = f.key + "-" + o.value;
                    const on = s.checked[f.key] === o.value;
                    return (
                      <div
                        key={o.value}
                        ref={reg("opt-" + id)}
                        className={"acceen-calc__option" + (s.hoverOption === id ? " is-hover" : "")}
                      >
                        <span className="acceen-calc__radio" ref={reg("radio-" + id)}>
                          {on ? <i /> : null}
                        </span>
                        <span>{o.label}</span>
                      </div>
                    );
                  })}
                </div>
              </div>
            </div>
          ))}
          <div ref={reg("field-nombre")}>
            <div className="acceen-calc__label">Combien de véhicules souhaitez-vous acquérir&nbsp;?*</div>
            {input("nombre", s.nombre)}
          </div>
        </div>
      </div>

      <div className="acceen-calc__contact" ref={reg("contact")}>
        <h2 className="acceen-calc__section-title" ref={reg("contact-title")}>
          Vos coordonnées
        </h2>
        <p className="acceen-calc__section-sub">
          Pour vous transmettre votre estimation et échanger sur votre projet. En demandant une
          estimation, vous acceptez d'être recontacté par nos équipes.
        </p>
        <div className="wpcf7-form">
          {contactField("firstName", "Prénom*", true)}
          {contactField("lastName", "Nom*", true)}
          {contactField("email", "E-mail*")}
          {contactField("phone", "Téléphone*")}
          {contactField("organisation", "Nom de la structure*")}
          {contactField("job", "Fonction*")}
          <p>
            <span className="wpcf7-accept">
              <span className={"wpcf7-check" + (s.accepted ? " is-on" : "")} ref={reg("checkbox")}>
                {s.accepted ? (
                  <svg width="14" height="14" viewBox="0 0 14 14">
                    <path d="M2.5 7.5l3 3 6-7" fill="none" stroke="#fff" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                  </svg>
                ) : null}
              </span>
              <span>
                J’accepte les <a>conditions générales d’utilisation</a>.*
              </span>
            </span>
          </p>
          <p ref={reg("submit-row")}>
            <span
              ref={reg("submit")}
              className={
                "wpcf7-submit" +
                (s.submitEnabled ? "" : " is-disabled") +
                (s.submitHover ? " is-hover" : "")
              }
            >
              Recevoir mon estimation
            </span>
          </p>
        </div>
      </div>
    </div>
  );
};

export const formatFr = (n: number) => new Intl.NumberFormat("fr-FR").format(Math.round(n));

export const ResultBlock: React.FC<{ value: number; fontSize: number }> = ({ value, fontSize }) => (
  <div className="acceen-calc__result">
    <div className="acceen-calc__hero">
      <div className="acceen-calc__eyebrow">Estimation de votre prime CEE</div>
      <div className="acceen-calc__value" style={{ fontSize }}>
        <span>{formatFr(value)}</span>
        <span className="acceen-calc__unit">€</span>
      </div>
      <p className="acceen-calc__note">
        Ce montant est une estimation indicative calculée à partir des critères renseignés. Il se
        base sur les conditions du 88ème arrêté (Octobre 2026) et sur un prix à 7,5€/MWhc. Nos
        experts vous recontacteront dans les 72h ouvrées afin d'échanger avec vous sur votre projet.
      </p>
    </div>
  </div>
);
