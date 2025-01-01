import React, { useState, useEffect } from "react";
import { HTTP_FORBIDDEN, jsonFetch } from "../functions/api";

const clamp = (n, min, max) => Math.min(Math.max(n, min), max);

const randomNumberBetween = (min, max) =>
    Math.floor(Math.random() * (max - min + 1) + min);

const PuzzleCaptcha = ({ src, width, height, pieceWidth, pieceHeight, inputName }) => {
    const [position, setPosition] = useState({
        x: randomNumberBetween(0, width - pieceWidth),
        y: randomNumberBetween(0, height - pieceHeight),
    });
    const [isDragging, setIsDragging] = useState(false);
    const [isLocked, setIsLocked] = useState(false);
    const [status, setStatus] = useState("waiting");

    // Récupération de la clé challenge dans l'URL
    const challengeKey = new URL(src).searchParams.get("challenge");

    const maxX = width - pieceWidth;
    const maxY = height - pieceHeight;

    const handlePointerDown = () => {
        if (isLocked) return;
        setIsDragging(true);
        setStatus("moving");
    };

    const handlePointerUp = async () => {
        if (!isDragging) return;

        setIsDragging(false);

        try {
            // Envoi de la requête à l'API pour valider la position
            await jsonFetch("/captcha/validate", {
                method: "POST",
                body: JSON.stringify({
                    key: challengeKey,
                    response: `${Math.round(position.x)}-${Math.round(position.y)}`,
                }),
            });

            setStatus("solved");
        } catch (e) {
            // En cas d'erreur API
            setStatus("error");
            if (e.status === HTTP_FORBIDDEN) {
                setTimeout(() => setStatus("loading"), 500);
            } else {
                setTimeout(() => setStatus("default"), 500);
            }
        }
    };

    const handlePointerMove = (e) => {
        if (!isDragging || isLocked) return;

        setPosition((prev) => ({
            x: clamp(prev.x + e.movementX, 0, maxX),
            y: clamp(prev.y + e.movementY, 0, maxY),
        }));
    };

    useEffect(() => {
        const handleMove = (e) => handlePointerMove(e);
        const handleUp = () => handlePointerUp();

        if (isDragging) {
            document.addEventListener("pointermove", handleMove);
            document.addEventListener("pointerup", handleUp);
        }

        return () => {
            document.removeEventListener("pointermove", handleMove);
            document.removeEventListener("pointerup", handleUp);
        };
    }, [isDragging, position]);

    return (
        <div
            className={`captcha captcha--${status}`}
            style={{
                "--width": `${width}px`,
                "--height": `${height}px`,
                "--image": `url(${src})`,
                "--pieceWidth": `${pieceWidth}px`,
                "--pieceHeight": `${pieceHeight}px`,
            }}
        >
            <div
                className="captcha-piece"
                onPointerDown={handlePointerDown}
                style={{
                    transform: `translate(${position.x}px, ${position.y}px)`,
                }}
            ></div>
            {/* Input caché contenant la valeur */}
            <input
                type="hidden"
                name={inputName}
                value={`${Math.round(position.x)}-${Math.round(position.y)}`}
            />
        </div>
    );
};

export default PuzzleCaptcha;
