import React, { useState, useEffect } from "react";
import { Loader, Check, Clock, PlayCircle } from "lucide-react";
import QuizResults from "./QuizResults";
import Question from "./Question";

const Quiz = ({ contentId, isUserLoggedIn }) => {
    const [quizzes, setQuizzes] = useState(undefined);
    const [currentQuiz, setCurrentQuiz] = useState(null);
    const [currentQuestionIndex, setCurrentQuestionIndex] = useState(0);
    const [score, setScore] = useState(0);
    const [timeLeft, setTimeLeft] = useState(0);
    const [isQuizStarted, setIsQuizStarted] = useState(false);
    const [userAnswers, setUserAnswers] = useState([]);
    const [quizFinished, setQuizFinished] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const [showResults, setShowResults] = useState(true);
    const [completedQuizzes, setCompletedQuizzes] = useState([]);
    const [selectedAnswers, setSelectedAnswers] = useState([]);
    const [showImmediateFeedback, setShowImmediateFeedback] = useState(false);
    const [isCorrectFeedback, setIsCorrectFeedback] = useState(null);
    const [answerSubmitted, setAnswerSubmitted] = useState(false);

    // Nouveaux états pour la sécurité
    const [sessionToken, setSessionToken] = useState(null);
    const [csrfToken, setCsrfToken] = useState(null);
    const [questionStartTimes, setQuestionStartTimes] = useState({});
    const [questionTimes, setQuestionTimes] = useState([]);

    useEffect(() => {
        if (contentId) {
            // Récupérer le token CSRF
            fetch('/api/csrf-token')
                .then(response => response.json())
                .then(data => setCsrfToken(data.token))
                .catch(error => console.error('Erreur CSRF:', error));

            fetch(`/api/quiz/${contentId}`)
                .then((response) => {
                    if (response.ok) {
                        if (response.status === 204) {
                            setQuizzes(null);
                            return null;
                        }
                        return response.json();
                    } else if (response.status === 404) {
                        setQuizzes(null);
                        return null;
                    } else {
                        throw new Error("Une erreur est survenue lors de la récupération des quiz.");
                    }
                })
                .then((data) => {
                    if (data) {
                        const quizzesData = Array.isArray(data) ? data : [data];
                        const sortedQuizzes = quizzesData.sort((a, b) =>
                            a.title.localeCompare(b.title)
                        );
                        setQuizzes(sortedQuizzes);
                    }
                })
                .catch((error) => {
                    console.error("Erreur lors de la récupération des quiz :", error);
                    setQuizzes(null);
                });

            if (!isUserLoggedIn) {
                const storedCompletedQuizzes =
                    JSON.parse(localStorage.getItem("completedQuizzes")) || [];
                setCompletedQuizzes(storedCompletedQuizzes);
            } else {
                fetch("/api/quiz/user/completed-quizzes")
                    .then((response) => response.json())
                    .then((data) => {
                        setCompletedQuizzes(data.completedQuizIds);
                    })
                    .catch((error) => {
                        console.error("Erreur lors de la récupération des quizzes complétés :", error);
                    });
            }
        }
    }, [contentId, isUserLoggedIn]);

    useEffect(() => {
        let timer;
        if (isQuizStarted && timeLeft > 0 && !isLoading && !answerSubmitted) {
            timer = setTimeout(() => setTimeLeft(timeLeft - 1), 1000);
        } else if (timeLeft === 0 && isQuizStarted && !answerSubmitted) {
            handleSubmitAnswer();
        }
        return () => clearTimeout(timer);
    }, [timeLeft, isQuizStarted, isLoading, answerSubmitted]);

    useEffect(() => {
        const handleBeforeUnload = (e) => {
            if (isQuizStarted && !quizFinished) {
                e.preventDefault();
                e.returnValue = "";
                return "";
            }
        };

        window.addEventListener("beforeunload", handleBeforeUnload);
        return () => {
            window.removeEventListener("beforeunload", handleBeforeUnload);
        };
    }, [isQuizStarted, quizFinished]);

    const startQuiz = async (quiz) => {
        setIsLoading(true);

        try {
            // Démarrer une session sécurisée
            const response = await fetch(`/api/secure-quiz/start/${quiz.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || 'Erreur lors du démarrage du quiz');
            }

            const sessionData = await response.json();
            setSessionToken(sessionData.sessionToken);

            setCurrentQuiz(quiz);
            setIsQuizStarted(true);
            setCurrentQuestionIndex(0);
            setQuizFinished(false);
            setScore(0);
            setUserAnswers([]);
            setShowResults(true);
            setSelectedAnswers([]);
            setShowImmediateFeedback(false);
            setIsCorrectFeedback(null);
            setAnswerSubmitted(false);
            setQuestionStartTimes({});
            setQuestionTimes([]);

            const initialTimeLimit = quiz.questions[0]?.timeLimit || 15;
            setTimeLeft(initialTimeLimit);

            // Enregistrer le temps de début de la première question
            setQuestionStartTimes(prev => ({
                ...prev,
                0: Date.now()
            }));

            document.body.style.overflow = "hidden";
        } catch (error) {
            console.error('Erreur lors du démarrage du quiz:', error);
            alert('Erreur lors du démarrage du quiz: ' + error.message);
        } finally {
            setIsLoading(false);
        }
    };

    const closeQuiz = () => {
        setShowResults(false);
        setCurrentQuiz(null);
        setQuizFinished(false);
        document.body.style.overflow = "";
    };

    const handleAnswerSelection = (answerId) => {
        if (answerSubmitted) return; // Pas de changement après soumission
        const currentQuestion = currentQuiz.questions[currentQuestionIndex];
        const isMultipleChoice = currentQuestion.type === "multiple_choice";

        if (isMultipleChoice) {
            setSelectedAnswers((prevSelected) => {
                if (prevSelected.includes(answerId)) {
                    return prevSelected.filter((id) => id !== answerId);
                } else {
                    return [...prevSelected, answerId];
                }
            });
        } else {
            // Pour single choice, une seule sélection possible
            setSelectedAnswers([answerId]);
        }
    };

    const handleSubmitAnswer = async () => {
        if (answerSubmitted) return;

        if (selectedAnswers.length === 0) {
            return;
        }

        setIsLoading(true);

        // Calculer le temps passé sur cette question
        const questionStartTime = questionStartTimes[currentQuestionIndex];
        const questionTime = questionStartTime ? Math.floor((Date.now() - questionStartTime) / 1000) : 0;

        // Mettre à jour les temps de réponse
        setQuestionTimes(prev => {
            const newTimes = [...prev];
            newTimes[currentQuestionIndex] = questionTime;
            return newTimes;
        });

        try {
            // Récupérer la vraie réponse du serveur
            const response = await fetch('/api/quiz/validate-answer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    quizId: currentQuiz.id,
                    questionIndex: currentQuestionIndex,
                    selectedAnswers: selectedAnswers,
                    questionTime: questionTime
                })
            });

            const result = await response.json();

            console.log('Réponse du serveur:', result);

            if (response.ok) {
                // S'assurer que correctAnswers est un tableau
                const correctAnswersArray = Array.isArray(result.correctAnswers)
                    ? result.correctAnswers
                    : Object.values(result.correctAnswers);

                // Stocker la réponse avec les vraies données du serveur
                setUserAnswers((prev) => [
                    ...prev,
                    {
                        questionIndex: currentQuestionIndex,
                        selected: selectedAnswers,
                        questionTime: questionTime,
                        isCorrect: result.isCorrect,
                        correctAnswers: correctAnswersArray,
                        explanation: result.explanation
                    }
                ]);

                // Mettre à jour le score
                if (result.isCorrect) {
                    console.log('Bonne réponse ! Score incrémenté');
                    setScore(prev => prev + 1);
                } else {
                    console.log('Mauvaise réponse');
                }

                // Afficher le vrai feedback
                setShowImmediateFeedback(true);
                setIsCorrectFeedback(result.isCorrect);
            } else {
                // En cas d'erreur, stocker sans validation
                setUserAnswers((prev) => [
                    ...prev,
                    {
                        questionIndex: currentQuestionIndex,
                        selected: selectedAnswers,
                        questionTime: questionTime,
                        isCorrect: false,
                        correctAnswers: [],
                        explanation: null
                    }
                ]);

                setShowImmediateFeedback(true);
                setIsCorrectFeedback(false);
            }
        } catch (error) {
            console.error('Erreur lors de la validation:', error);

            // En cas d'erreur, stocker sans validation
            setUserAnswers((prev) => [
                ...prev,
                {
                    questionIndex: currentQuestionIndex,
                    selected: selectedAnswers,
                    questionTime: questionTime,
                    isCorrect: false,
                    correctAnswers: [],
                    explanation: null
                }
            ]);

            setShowImmediateFeedback(true);
            setIsCorrectFeedback(false);
        }

        setAnswerSubmitted(true);
        setIsLoading(false);
    };

    const handleNextQuestion = () => {
        // Ne pas réinitialiser le feedback immédiatement pour la dernière question
        if (currentQuestionIndex < currentQuiz.questions.length - 1) {
            setShowImmediateFeedback(false);
            setIsCorrectFeedback(null);
            setAnswerSubmitted(false);

            const nextQuestionIndex = currentQuestionIndex + 1;
            setCurrentQuestionIndex(nextQuestionIndex);
            const nextTimeLimit =
                currentQuiz.questions[nextQuestionIndex]?.timeLimit || 15;
            setTimeLeft(nextTimeLimit);
            setSelectedAnswers([]);

            // Enregistrer le temps de début de la prochaine question
            setQuestionStartTimes(prev => ({
                ...prev,
                [nextQuestionIndex]: Date.now()
            }));
        } else {
            // Pour la dernière question, attendre un peu avant de passer aux résultats
            setTimeout(() => {
                setIsQuizStarted(false);
                setQuizFinished(true);
            }, 2000); // 2 secondes pour voir le feedback
        }
    };

    const handleSkipQuestion = () => {
        // Calculer le temps passé sur cette question
        const questionStartTime = questionStartTimes[currentQuestionIndex];
        const questionTime = questionStartTime ? Math.floor((Date.now() - questionStartTime) / 1000) : 0;

        // Mettre à jour les temps de réponse
        setQuestionTimes(prev => {
            const newTimes = [...prev];
            newTimes[currentQuestionIndex] = questionTime;
            return newTimes;
        });

        // Stocker la réponse (question sautée)
        setUserAnswers((prev) => [
            ...prev,
            {
                questionIndex: currentQuestionIndex,
                selected: [],
                questionTime: questionTime
            }
        ]);
        handleNextQuestion();
    };

    const submitScore = async () => {
        if (!isUserLoggedIn) {
            alert("Veuillez vous connecter pour soumettre votre score.");
            return;
        }

        // Mode test : calculer le score localement pour l'affichage
        if (!sessionToken || !csrfToken) {
            console.log("Mode test : calcul local du score");

            // Calculer le score localement pour l'affichage
            let localScore = 0;
            const updatedAnswers = userAnswers.map((answer, index) => {
                // Pour le mode test, on simule des réponses correctes
                // En réalité, le vrai calcul se fait côté serveur
                const isCorrect = Math.random() > 0.3; // Simulation pour le test
                if (isCorrect) localScore++;

                return {
                    ...answer,
                    isCorrect: isCorrect,
                    correctAnswers: isCorrect ? answer.selected : []
                };
            });

            setScore(localScore);
            setUserAnswers(updatedAnswers);
            setCompletedQuizzes((prev) => [...prev, currentQuiz.id]);

            alert(`Mode test - Score: ${localScore}/${currentQuiz.questions.length}`);
            closeQuiz();
            return;
        }

        setIsLoading(true);

        try {
            // Préparer les données pour la soumission sécurisée
            const answersData = userAnswers.map(answer => answer.selected);

            const response = await fetch('/api/secure-quiz/submit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    sessionToken: sessionToken,
                    answers: answersData,
                    questionTimes: questionTimes,
                    csrf_token: csrfToken
                })
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.error || 'Erreur lors de la soumission');
            }

            // Mettre à jour le score avec celui calculé côté serveur
            setScore(result.score);

            setCompletedQuizzes((prev) => [...prev, currentQuiz.id]);

            // Afficher les résultats détaillés avec les bonnes réponses
            setUserAnswers(prev => prev.map((answer, index) => ({
                ...answer,
                isCorrect: result.detailedResults[index]?.isCorrect || false,
                correctAnswers: result.detailedResults[index]?.correctAnswers || []
            })));

            alert(`Score soumis avec succès ! Score: ${result.score}/${result.totalQuestions} (${result.percentage}%)`);
            closeQuiz();
        } catch (error) {
            console.error("Erreur lors de la soumission du score :", error);
            alert("Erreur lors de la soumission: " + error.message);
        } finally {
            setIsLoading(false);
        }
    };

    const completeQuizWithoutSubmitting = () => {
        if (isUserLoggedIn) {
            fetch(`/api/quiz/${currentQuiz.id}/complete`, {
                method: "POST"
            })
                .then((response) => response.json())
                .then(() => {
                    setCompletedQuizzes((prev) => [...prev, currentQuiz.id]);
                    closeQuiz();
                })
                .catch((error) => {
                    console.error("Erreur lors du marquage du quiz comme complété :", error);
                });
        } else {
            const storedCompletedQuizzes =
                JSON.parse(localStorage.getItem("completedQuizzes")) || [];
            const updatedCompletedQuizzes = [...storedCompletedQuizzes, currentQuiz.id];
            localStorage.setItem(
                "completedQuizzes",
                JSON.stringify(updatedCompletedQuizzes)
            );
            setCompletedQuizzes(updatedCompletedQuizzes);
            closeQuiz();
        }
    };

    const redirectToSignup = () => {
        window.location.href = "/inscription";
    };

    if (quizzes === undefined) {
        return (
            <p className="text-center text-gray-500 mt-10">
                Chargement des quiz...
            </p>
        );
    }

    if (quizzes === null || quizzes.length === 0) {
        return null;
    }

    // Affichage du quiz en cours
    if (currentQuiz) {
        if (quizFinished) {
            return (
                <div className="fixed inset-0 z-50 bg-white dark:bg-primary-950 p-4 overflow-auto">
                    <div className="w-full max-w-3xl mx-auto py-6 px-6 border border-slate-200 dark:border-slate-800 shadow-lg rounded-md bg-white dark:bg-primary-950">
                        {showResults ? (
                            <QuizResults
                                currentQuiz={currentQuiz}
                                userAnswers={userAnswers}
                                score={score}
                                isUserLoggedIn={isUserLoggedIn}
                                submitScore={submitScore}
                                completeQuizWithoutSubmitting={completeQuizWithoutSubmitting}
                                closeQuiz={closeQuiz}
                                redirectToSignup={redirectToSignup}
                            />
                        ) : (
                            <div className="text-center">
                                <p className="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">
                                    Merci d'avoir participé au quiz !
                                </p>
                                <button
                                    onClick={closeQuiz}
                                    className="btn btn-light"
                                    aria-label="Retour à la liste des quiz"
                                >
                                    Retour à la liste des quiz
                                </button>
                            </div>
                        )}
                    </div>
                </div>
            );
        }

        const currentQuestion = currentQuiz.questions[currentQuestionIndex];
        const questionCount = currentQuiz.questions.length;

        return (
            <div className="fixed inset-0 z-50 bg-white dark:bg-primary-950 p-4 overflow-auto">
                <div className="w-full max-w-3xl mx-auto py-6 px-6 border border-slate-200 dark:border-slate-800 shadow-lg rounded bg-white dark:bg-primary-950">
                    {isLoading ? (
                        <div className="flex flex-col items-center">
                            <Loader className="w-12 h-12 animate-spin text-primary-500 dark:text-primary-300 mb-4" />
                            <p className="text-lg font-medium text-gray-800 dark:text-gray-200">
                                Chargement...
                            </p>
                        </div>
                    ) : (
                        <Question
                            currentQuestion={{
                                ...currentQuestion,
                                correctAnswers: Array.isArray(userAnswers[currentQuestionIndex]?.correctAnswers)
                                    ? userAnswers[currentQuestionIndex].correctAnswers
                                    : []
                            }}
                            currentQuestionIndex={currentQuestionIndex}
                            questionCount={questionCount}
                            selectedAnswers={selectedAnswers}
                            handleAnswerSelection={handleAnswerSelection}
                            handleSubmitAnswer={handleSubmitAnswer}
                            handleSkipQuestion={handleSkipQuestion}
                            timeLeft={timeLeft}
                            score={score}
                            isCorrectFeedback={isCorrectFeedback}
                            showImmediateFeedback={showImmediateFeedback}
                            isMultipleChoice={currentQuestion.type === "multiple_choice"}
                            handleNextQuestion={handleNextQuestion}
                            answerSubmitted={answerSubmitted}
                        />
                    )}
                </div>
            </div>
        );
    }

    // -----------------------
    // LISTE DES QUIZ
    // -----------------------
    return (
        <div className="my-8 flex flex-col items-center justify-start">
            {/* Titre et intro */}
            <div className="w-full text-left mb-6">
                <h2 className="text-2xl sm:text-3xl font-medium">
                    Prêt à tester vos connaissances&nbsp;?
                </h2>
                <p className="text-base text-gray-600 dark:text-gray-300">
                    Explorez ces quiz et mettez vos compétences à l’épreuve.
                </p>
            </div>

            <div className="w-full max-w-4xl">
                <div
                    className={`grid ${
                        quizzes.length === 1
                            ? "grid-cols-1"
                            : "grid-cols-1 sm:grid-cols-2 lg:grid-cols-3"
                    } gap-5`}
                >
                    {quizzes.map((quiz) => {
                        const isCompleted = completedQuizzes.includes(quiz.id);

                        const questionCount = quiz.questions?.length || 0;
                        const totalTimeSeconds = quiz.questions?.reduce(
                            (acc, q) => acc + (q.timeLimit || 15),
                            0
                        ) || 0;
                        const totalTimeMinutes = Math.ceil(totalTimeSeconds / 60);

                        return (
                            <div
                                key={quiz.id}
                                className={`
                  relative bg-white dark:bg-primary-950
                  rounded border border-slate-200 dark:border-slate-700
                  p-5 transition-colors
                  ${isCompleted ? "opacity-80" : ""}
                `}
                            >
                                {isCompleted && (
                                    <span className="absolute top-3 right-3 text-green-500">
                    <Check className="w-6 h-6" />
                  </span>
                                )}

                                {/* Titre */}
                                <h3 className="text-lg font-medium text-gray-800 dark:text-gray-100 mb-1 line-clamp-2">
                                    {quiz.title}
                                </h3>

                                {/* Description (facultative) */}
                                {quiz.description && (
                                    <p className="text-sm text-gray-600 dark:text-gray-300 mb-3 line-clamp-3">
                                        {quiz.description}
                                    </p>
                                )}

                                {/* Infos (questions & durée) */}
                                <div className="flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400 mt-auto">
                                    <span>{questionCount} questions</span>
                                    <div className="w-1 h-1 bg-gray-300 dark:bg-gray-600 rounded-full" />
                                    <div className="flex items-center gap-1">
                                        <Clock className="w-4 h-4" />
                                        <span>~ {totalTimeMinutes} min</span>
                                    </div>
                                </div>

                                {/* Footer : bouton ou label */}
                                <div className="flex justify-end mt-3">
                                    {isCompleted ? (
                                        <p className="inline-block px-3 py-1 text-xs font-medium rounded bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200">
                                            Quiz complété
                                        </p>
                                    ) : (
                                        <button
                                            onClick={() => startQuiz(quiz)}
                                            className="btn btn-primary"
                                            aria-label={`Commencer le quiz ${quiz.title}`}
                                        >
                                            <PlayCircle />
                                            Commencer
                                        </button>
                                    )}
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>
        </div>
    );
};

export default Quiz;
