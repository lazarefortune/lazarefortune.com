export const playerStyle = `
:host {
  display: block;
  --primary-color: #14213D;
  --accent-color: #FCA311;
  --font-color: #FFFFFF;
  --font-family: sans-serif;
  --max-height: 100vh;
}

.ratio {
  background-color: black;
  position: relative;
}

.ratio-svg {
  width: 100%;
  height: auto;
  display: block;
  max-height: var(--max-height);
}

video ~ .ratio-svg,
iframe ~ .ratio-svg {
  max-height: 100%;
}

.poster {
  border: none;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  transition: transform 0.3s, opacity 0.5s;
}

.poster .play{
    transform: scale(1.2);
    filter: drop-shadow(0 2px 24px var(--accent-color));
}
.poster::before {
    opacity: 0.8;
}
.poster .title {
  transform: translateY(0);
  opacity: 1;
}

.title {
  margin-top: 4px;
  color: var(--font-color);
  font-size: 22px;
  position: relative;
  text-align: center;
  z-index: 3;
  line-height: 1.2em;
  text-shadow: 0px 0px 20px #000;
  font-family: var(--font-family);
  transition: opacity 0.3s, transform 0.3s;
  transform: translateY(10px);
  opacity: 0;
}

// .poster:hover .play {
//   transform: scale(1.2);
//   filter: drop-shadow(0 2px 24px var(--accent-color));
// }
//
// .poster:hover::before {
//   opacity: 0.8;
// }
//
// .poster:hover .title {
//   transform: translateY(0);
//   opacity: 1;
// }

.title em {
  display: block;
  opacity: 0.7;
  font-size: 0.8em;
  font-style: normal;
}

.play {
  position: relative;
  width: 48px;
  height: 48px;
  z-index: 3;
  fill: var(--font-color);
  margin-bottom: 8px;
  filter: drop-shadow(0 1px 20px #121C4280);
  transition: 0.3s;
}

.poster::before {
  content: '';
  background: linear-gradient(to top, #121c42 0%, rgba(18, 28, 66, 0) 100%);
  z-index: 2;
}

.poster,
iframe,
video,
.poster::before,
img {
  position: absolute;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  width: 100%;
  height: 100%;
  transition: opacity 0.5s;
}

.poster img {
  object-fit: cover;
}

.poster[aria-hidden] {
  pointer-events: none;
  opacity: 0;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
}

.play {
  animation: pulse 1.5s infinite;
}
`;
