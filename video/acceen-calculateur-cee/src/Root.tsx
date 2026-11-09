import React from "react";
import { Composition } from "remotion";
import { Video } from "./Video";
import { FPS, DURATION, WIDTH, HEIGHT } from "./config";

export const RemotionRoot: React.FC = () => (
  <Composition
    id="CalculateurCEE"
    component={Video}
    durationInFrames={DURATION}
    fps={FPS}
    width={WIDTH}
    height={HEIGHT}
  />
);
