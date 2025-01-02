import React, { useState, useEffect } from "react";
import { CheckCircle } from "lucide-react";
import { HTTP_FORBIDDEN, jsonFetch } from "../functions/api";

const clamp = (n, min, max) => Math.min(Math.max(n, min), max);

const randomNumberBetween = (min, max) =>
    Math.floor(Math.random() * (max - min + 1) + min);

const PuzzleCaptcha = ({ src, width, height, pieceWidth, pieceHeight, answerInputName, challengeInputName }) => {
    const [position, setPosition] = useState({
        x: randomNumberBetween(0, width - pieceWidth),
        y: randomNumberBetween(0, height - pieceHeight),
    });
    const [isDragging, setIsDragging] = useState(false);
    const [isLocked, setIsLocked] = useState(false);
    const [status, setStatus] = useState("waiting");
    const [hasMoved, setHasMoved] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const [currentSrc, setCurrentSrc] = useState(src);
    const [challengeKey, setChallengeKey] = useState(new URL(src).searchParams.get("challenge"));

    const maxX = width - pieceWidth;
    const maxY = height - pieceHeight;

    const updateSrcWithChallengeKey = (newKey) => {
        const url = new URL(currentSrc);
        url.searchParams.set("challenge", newKey);
        setCurrentSrc(url.toString());
    };

    const handlePointerDown = () => {
        if (isLocked || isLoading) return;
        setIsDragging(true);
        setStatus("moving");
        setHasMoved(false);
    };

    const handlePointerUp = async () => {
        if (!isDragging) return;

        setIsDragging(false);

        if (!hasMoved) {
            setStatus("waiting");
            return;
        }

        try {
            await jsonFetch("/captcha/validate", {
                method: "POST",
                body: JSON.stringify({
                    key: challengeKey,
                    response: `${Math.round(position.x)}-${Math.round(position.y)}`,
                }),
            });

            setStatus("solved");
        } catch (e) {
            if (e.status === HTTP_FORBIDDEN && e.data?.newKey) {
                setStatus("error");
                setTimeout(() => {
                    setIsLoading(true); // Afficher le loader
                    setStatus("waiting");
                    const newKey = e.data.newKey;

                    // Mettre à jour la clé du challenge et réinitialiser le captcha
                    setChallengeKey(newKey);
                    updateSrcWithChallengeKey(newKey);

                    setPosition({
                        x: randomNumberBetween(0, maxX),
                        y: randomNumberBetween(0, maxY),
                    });

                    setTimeout(() => setIsLoading(false), 1000); // Masquer le loader après le chargement
                }, 500); // Attendre l'animation d'échec
            } else {
                setStatus("error");
                setTimeout(() => setStatus("default"), 500);
            }
        }
    };

    const handlePointerMove = (e) => {
        if (!isDragging || isLocked) return;

        setHasMoved(true);

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
        <div className="w-full flex flex-col justify-center items-center relative">
            {isLoading && (
                <div className="absolute inset-0 flex justify-center items-center bg-white/50 dark:bg-black/50 z-10">
                    <div className="captcha-loader"/>
                </div>
            )}
            <div
                className={`captcha captcha--${status}`}
                style={{
                    "--width": `${width}px`,
                    "--height": `${height}px`,
                    "--image": `url(${currentSrc}`,
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

                {status === "solved" && (
                    <div className="captcha-check-icon">
                        <CheckCircle color="white" size={48} />
                    </div>
                )}
            </div>
            <div className="text-sm text-slate-600 dark:text-slate-300 mt-2">
                Placez la pièce du puzzle si vous n'êtes pas un robot
            </div>

            <input
                type="hidden"
                name={answerInputName}
                value={`${Math.round(position.x)}-${Math.round(position.y)}`}
            />

            <input
                type="hidden"
                name={challengeInputName}
                value={challengeKey}
            />
        </div>
    );
};

export default PuzzleCaptcha;
