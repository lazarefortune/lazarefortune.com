import React, { useState, useEffect, useRef, useCallback } from "react";
import ReactDOM from "react-dom";
import { Loader } from "lucide-react";

// Endpoints (à adapter si besoin)
const SEARCH_URL = "/recherche";   // Page complète de recherche
const SEARCH_API = "/api/search";  // API renvoyant un JSON de la forme { items: [...], hits: number }

/**
 * Fonction debounce pour limiter les appels API.
 */
function debounce(fn, delay) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}

/**
 * Fonction pour échapper une chaîne afin de construire une RegExp.
 */
function escapeRegExp(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

/**
 * Applique le highlight sur les correspondances.
 * Les occurrences du `query` dans le texte seront entourées d'une balise <mark>.
 */
function highlightMatches(text, query) {
    if (!query) return text;
    const regex = new RegExp(`(${escapeRegExp(query)})`, "gi");
    return text.replace(regex, "<mark>$1</mark>");
}

/**
 * Formate le résultat en appliquant la surbrillance sur le titre, sauf si l'élément est marqué comme extra.
 */
function formatResult(item, query) {
    if (item.extra) return item.title; // Ne pas appliquer de highlight sur l'élément extra
    if (item.category) {
        return `<span style="font-size: 0.8rem; color: #999;">${item.category}</span><br>${highlightMatches(item.title, query)}`;
    }
    return highlightMatches(item.title, query);
}

/**
 * Composant principal.
 * Affiche un input en lecture seule et un bouton qui déclenchent tous les deux l'ouverture de la modale.
 */
export function Search() {
    const [isSearchVisible, setSearchVisible] = useState(false);

    const toggleSearchBar = () => setSearchVisible((prev) => !prev);

    // Raccourci clavier : Ctrl+K ou Ctrl+Espace pour ouvrir/fermer la recherche
    useEffect(() => {
        const handler = (e) => {
            if ((e.key === "k" || e.key === " ") && e.ctrlKey) {
                e.preventDefault();
                toggleSearchBar();
            }
        };
        window.addEventListener("keydown", handler);
        return () => window.removeEventListener("keydown", handler);
    }, []);

    // Désactive le scroll du body quand la recherche est ouverte
    useEffect(() => {
        document.body.style.overflow = isSearchVisible ? "hidden" : "";
        return () => {
            document.body.style.overflow = "";
        };
    }, [isSearchVisible]);

    return (
        <div className="relative flex items-center">
            {/* Conteneur déclencheur */}
            <div onClick={toggleSearchBar} className="relative flex items-center cursor-pointer">
                <input
                    type="text"
                    placeholder="Rechercher..."
                    readOnly
                    className="hidden md:block bg-slate-50 border-slate-200 dark:bg-primary-1000 dark:border-slate-800 px-3 py-2 rounded-xl cursor-pointer pr-10"
                />
                <button
                    aria-label="Rechercher"
                    onClick={(e) => {
                        e.stopPropagation(); // pour éviter la propagation du clic sur le conteneur parent
                        toggleSearchBar();
                    }}
                    className="absolute inset-y-0 right-2 flex items-center text-slate-950 md:text-slate-400 dark:text-white  dark:md:text-slate-700"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        strokeWidth={2}
                        stroke="currentColor"
                        className="w-6 h-6"
                    >
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z"
                        />
                    </svg>
                </button>
            </div>
            {isSearchVisible && <SearchBar onClose={() => setSearchVisible(false)} />}
        </div>
    );
}

/**
 * Composant SearchBar : overlay de la recherche.
 * Utilise createPortal pour afficher l’overlay par-dessus tout.
 */
function SearchBar({ onClose }) {
    useEffect(() => {
        const handleEscape = (e) => {
            if (e.key === "Escape") {
                e.preventDefault();
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
                <div className="search-footer hidden sm:block">
                    <span>Entrée</span> pour sélectionner, <span>Échap</span> pour fermer, <span>↑</span> et <span>↓</span> pour naviguer
                </div>
            </div>
        </div>,
        document.body
    );
}

/**
 * Composant SearchInput : le formulaire de recherche avec suggestions.
 */
export function SearchInput({ onClose }) {
    const [query, setQuery] = useState("");
    const [results, setResults] = useState([]);
    const [hits, setHits] = useState(0);
    const [loading, setLoading] = useState(false);
    const [selectedIndex, setSelectedIndex] = useState(null);
    const inputRef = useRef(null);
    const resultsRef = useRef([]);

    useEffect(() => {
        inputRef.current?.focus();
    }, []);

    useEffect(() => {
        if (selectedIndex !== null && resultsRef.current[selectedIndex]) {
            resultsRef.current[selectedIndex].scrollIntoView({
                block: "nearest",
            });
        }
    }, [selectedIndex]);

    const suggest = useCallback(
        debounce(async (value) => {
            if (!value) {
                setResults([]);
                setHits(0);
                return;
            }
            setLoading(true);
            try {
                const response = await fetch(`${SEARCH_API}?q=${encodeURIComponent(value)}`);
                if (!response.ok) {
                    setLoading(false);
                    return;
                }
                const data = await response.json();
                setResults(data.items || []);
                setHits(data.hits || 0);
                setSelectedIndex(null);
            } catch (e) {
                console.error(e);
            } finally {
                setLoading(false);
            }
        }, 300),
        []
    );

    const handleInput = (e) => {
        const value = e.target.value;
        setQuery(value);
        suggest(value);
    };

    // Calcul du nombre total d'éléments affichés : ajoute 1 si on affiche l'élément extra.
    const totalDisplayed =
        query !== "" && hits > results.length ? results.length + 1 : results.length;

    const handleKeyDown = (e) => {
        if (totalDisplayed === 0) return;
        switch (e.key) {
            case "ArrowDown":
                e.preventDefault();
                setSelectedIndex((prev) =>
                    prev === null ? 0 : Math.min(prev + 1, totalDisplayed - 1)
                );
                break;
            case "ArrowUp":
                e.preventDefault();
                setSelectedIndex((prev) =>
                    prev === null ? totalDisplayed - 1 : Math.max(prev - 1, 0)
                );
                break;
            case "Enter":
                if (selectedIndex !== null) {
                    e.preventDefault();
                    // Si l'élément extra est sélectionné, rediriger vers la page complète
                    if (
                        query !== "" &&
                        hits > results.length &&
                        selectedIndex === totalDisplayed - 1
                    ) {
                        window.location.href = `${SEARCH_URL}?q=${encodeURIComponent(query)}&redirect=0`;
                    } else {
                        window.location.href = results[selectedIndex]?.url || "/";
                    }
                }
                break;
            default:
                break;
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        if (selectedIndex !== null && results[selectedIndex]) {
            window.location.href = results[selectedIndex].url;
        } else {
            window.location.href = `${SEARCH_URL}?q=${encodeURIComponent(query)}`;
        }
    };

    // Construction de l'array affiché incluant l'élément "Voir les X résultats"
    let displayedResults = [...results];
    if (query !== "" && hits > results.length) {
        displayedResults.push({
            title: `Voir les <strong>${hits}</strong> résultats`,
            url: `${SEARCH_URL}?q=${encodeURIComponent(query)}&redirect=0`,
            category: "",
            extra: true, // Marque cet élément pour ne pas appliquer le highlight
        });
    }

    return (
        <div className="search-bar">
            <form onSubmit={handleSubmit}>
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

                {displayedResults.length > 0 && (
                    <div className="search-suggestions">
                        {displayedResults.map((result, index) => {
                            const isSelected = index === selectedIndex;
                            return (
                                <div key={index}>
                                    <a
                                        ref={(el) => (resultsRef.current[index] = el)}
                                        href={result.url}
                                        className={
                                            isSelected
                                                ? "block px-4 py-2 rounded-md text-base bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-white"
                                                : "block px-4 py-2 rounded-md text-base hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-700"
                                        }
                                        dangerouslySetInnerHTML={{
                                            __html: formatResult(result, query),
                                        }}
                                    />
                                </div>
                            );
                        })}
                    </div>
                )}
            </form>
        </div>
    );
}

export default Search;
