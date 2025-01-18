import React, { useState, useEffect, useRef, useCallback } from "react";
import ReactDOM from "react-dom";
import { Search as SearchIcon, Loader, Video } from "lucide-react";

const SEARCH_API = "/api/search";

export function Search() {
    const [isSearchVisible, setSearchVisible] = useState(false);

    const toggleSearchBar = () => setSearchVisible((prev) => !prev);

    useEffect(() => {
        const handler = (e) => {
            if (e.ctrlKey && e.key === "k") {
                e.preventDefault();
                toggleSearchBar();
            }
        };
        window.addEventListener("keydown", handler);
        return () => window.removeEventListener("keydown", handler);
    }, []);

    useEffect(() => {
        if (isSearchVisible) {
            document.body.style.overflow = "hidden"; // Désactive le défilement
        } else {
            document.body.style.overflow = ""; // Réinitialise le défilement
        }

        return () => {
            document.body.style.overflow = ""; // Réinitialise au cas où
        };
    }, [isSearchVisible]);

    return (
        <>
            <button onClick={toggleSearchBar} aria-label="Rechercher" className="border-2 border-primary-600 text-primary-600 p-3 rounded-full ">
                <SearchIcon size={17}/>
            </button>
            {isSearchVisible && <SearchBar onClose={toggleSearchBar}/>}
        </>
    );
}

function SearchInput({ onClose }) {
    const inputRef = useRef(null);
    const [ query, setQuery ] = useState("");
    const [ results, setResults] = useState([]);
    const [loading, setLoading] = useState(false);
    const [selectedIndex, setSelectedIndex] = useState(null);

    const suggest = useCallback(
        debounce(async (value) => {
            if (!value) return;
            setLoading(true);
            const response = await fetch(`${SEARCH_API}/${encodeURIComponent(value)}`);
            const data = await response.json();
            setResults(data || []);
            // setResults(data.items || []);
            setLoading(false);
        }, 300),
        []
    );

    const handleInput = (e) => {
        const value = e.target.value;
        setQuery(value);
        suggest(value);
    };

    const handleKeyDown = (e) => {
        if (!results.length) return;

        if (e.key === "ArrowDown") {
            e.preventDefault();
            setSelectedIndex((prev) => (prev === null ? 0 : Math.min(prev + 1, results.length - 1)));
        } else if (e.key === "ArrowUp") {
            e.preventDefault();
            setSelectedIndex((prev) => (prev === null ? results.length - 1 : Math.max(prev - 1, 0)));
        } else if (e.key === "Enter" && selectedIndex !== null) {
            e.preventDefault();
            window.location.href = results[selectedIndex]?.url || "/";
        }
    };

    useEffect(() => {
        inputRef.current?.focus();
    }, []);

    return (
        <div className="search-bar">
            <div className="search-input-wrapper">
                <div className="search-icon">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        strokeWidth={2}
                        stroke="currentColor"
                        className="w-5 h-5"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z"
                        />
                    </svg>
                </div>
                <input
                    type="text"
                    ref={inputRef}
                    value={query}
                    onChange={handleInput}
                    onKeyDown={handleKeyDown}
                    placeholder="Rechercher..."
                />
            </div>
            {loading && (
                <div className="search-loader">
                    <Loader size={20} />
                </div>
            )}
            {results.length > 0 && (
                <div className="search-suggestions">
                    {results.map((result, index) => (
                        <div
                            key={index}
                            className={index === selectedIndex ? "active" : ""}
                        >
                            <a href={result.url} className="flex gap-2">
                                <div className="relative flex-shrink-0 w-40 h-auto rounded overflow-hidden">
                                    {result.image ? (
                                        <img
                                            src={result.image.url}
                                            alt={result.title}
                                            className="w-full h-auto object-cover rounded shadow-sm"
                                        />
                                    ) : (
                                        <div className="relative w-full h-24 bg-gray-200 dark:bg-slate-600">
                                            <Video size={24} className="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500" />
                                        </div>
                                    )}
                                </div>

                                {result.title}
                            </a>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}

function SearchBar({ onClose }) {
    useEffect(() => {
        const handleEscape = (e) => {
            if (e.key === "Escape") {
                e.preventDefault(); // Empêche le comportement par défaut
                onClose();
            }
        };
        window.addEventListener("keydown", handleEscape);
        return () => window.removeEventListener("keydown", handleEscape);
    }, [onClose]);

    return ReactDOM.createPortal(
        <div className="search-overlay active" onClick={onClose}>
            <div className="search-container" onClick={(e) => e.stopPropagation()}>
                <SearchInput onClose={onClose} />
                <div className="search-footer">
                    Appuyez sur <span>Échap</span> pour fermer
                </div>
            </div>
        </div>,
        document.body
    );
}


// Fonction debounce pour limiter les appels à l'API
function debounce(fn, delay) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}
