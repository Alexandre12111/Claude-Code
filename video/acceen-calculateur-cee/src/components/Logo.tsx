import React from "react";
import { Img, staticFile } from "remotion";

export const LogoHorizontal: React.FC<{ width: number; style?: React.CSSProperties }> = ({ width, style }) => (
  <Img src={staticFile("logo-purple-horizontal.svg")} style={{ width, height: (width * 317) / 1081, ...style }} />
);

export const LogoIcon: React.FC<{ height: number; style?: React.CSSProperties }> = ({ height, style }) => (
  <Img src={staticFile("logo-purple-icon.svg")} style={{ height, ...style }} />
);
