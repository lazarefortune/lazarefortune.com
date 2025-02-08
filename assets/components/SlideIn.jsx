import React, { useEffect, useState } from "react";

export function SlideIn({ show, children, className = "", style = {}, forwardedRef = null, ...props }) {
    const [shouldRender, setRender] = useState(show);

    useEffect(() => {
        if (show) setRender(true);
    }, [show]);

    const onAnimationEnd = e => {
        if (!show && e.animationName === "slideOut") setRender(false);
    };

    // Concaténer "is-open" à la classe si show est vrai
    const finalClassName = `${className} ${show ? "is-open" : ""}`;

    return (
        shouldRender && (
            <div
                style={{ animation: `${show ? "slideIn" : "slideOut"} .3s both`, ...style }}
                onAnimationEnd={onAnimationEnd}
                className={finalClassName}
                ref={forwardedRef}
                {...props}
            >
                {children}
            </div>
        )
    );
}
