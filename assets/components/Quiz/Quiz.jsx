import React, { useState, useEffect } from "react";
import { CheckCircle, XCircle, Loader, Timer, Check } from "lucide-react";

const Quiz = ({ contentId, isUserLoggedIn }) => {
    const [quizzes, setQuizzes] = useState(undefined); // Liste des quiz
    const [currentQuiz, setCurrentQuiz] = useState(null); // Quiz sélectionné
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

    useEffect(() => {
        if (contentId) {
            fetch(`/api/quiz/${contentId}`)
                .then((response) => {
                    if (response.ok) {
                        return response.json();
                    } else if (response.status === 404) {
                        // Si aucun quiz n'existe, on définit quizzes à null
                        setQuizzes(null);
                        return null;
                    } else {
                        throw new Error('Une erreur est survenue lors de la récupération des quiz.');
                    }
                })
                .then((data) => {
                    if (data) {
                        // Si un seul quiz est renvoyé, on le place dans un tableau
                        const quizzesData = Array.isArray(data) ? data : [data];

                        // Trier les quiz par titre ou autre critère si nécessaire
                        const sortedQuizzes = quizzesData.sort((a, b) => a.title.localeCompare(b.title));

                        setQuizzes(sortedQuizzes);
                    }
                })
                .catch((error) => {
                    console.error("Erreur lors de la récupération des quiz :", error);
                    setQuizzes(null);
                });

            if (!isUserLoggedIn) {
                const storedCompletedQuizzes = JSON.parse(localStorage.getItem('completedQuizzes')) || [];
                setCompletedQuizzes(storedCompletedQuizzes);
            } else {
                // Récupérer les quizzes complétés pour l'utilisateur connecté
                fetch('/api/quiz/user/completed-quizzes')
                    .then(response => response.json())
                    .then(data => {
                        setCompletedQuizzes(data.completedQuizIds);
                    })
                    .catch(error => {
                        console.error('Erreur lors de la récupération des quizzes complétés :', error);
                    });
            }
        }
    }, [contentId, isUserLoggedIn]);

    useEffect(() => {
        let timer;
        if (isQuizStarted && timeLeft > 0 && !isLoading) {
            timer = setTimeout(() => setTimeLeft(timeLeft - 1), 1000);
        } else if (timeLeft === 0 && isQuizStarted) {
            handleSubmitAnswer();
        }
        return () => clearTimeout(timer);
    }, [timeLeft, isQuizStarted, isLoading]);

    useEffect(() => {
        const handleBeforeUnload = (e) => {
            if (isQuizStarted && !quizFinished) {
                e.preventDefault();
                e.returnValue = '';
                return '';
            }
        };

        window.addEventListener('beforeunload', handleBeforeUnload);

        return () => {
            window.removeEventListener('beforeunload', handleBeforeUnload);
        };
    }, [isQuizStarted, quizFinished]);

    const startQuiz = (quiz) => {
        setCurrentQuiz(quiz);
        setIsQuizStarted(true);
        setCurrentQuestionIndex(0);
        setQuizFinished(false);
        setScore(0);
        setUserAnswers([]);
        setIsLoading(false);
        setShowResults(true);
        setSelectedAnswers([]);

        // Initialiser timeLeft avec le timeLimit de la première question
        const initialTimeLimit = quiz.questions[0]?.timeLimit || 15;
        setTimeLeft(initialTimeLimit);
    };

    const handleAnswerSelection = (answerId) => {
        const currentQuestion = currentQuiz.questions[currentQuestionIndex];
        const isMultipleChoice = currentQuestion.type === "multiple_choice";

        if (isMultipleChoice) {
            // Pour les questions à choix multiple, on ajoute ou retire l'ID de la réponse sélectionnée
            setSelectedAnswers((prevSelected) => {
                if (prevSelected.includes(answerId)) {
                    // Si la réponse est déjà sélectionnée, on la retire
                    return prevSelected.filter((id) => id !== answerId);
                } else {
                    // Sinon, on l'ajoute
                    return [...prevSelected, answerId];
                }
            });
        } else {
            // Pour les questions à choix unique, on ne permet qu'une seule sélection
            setSelectedAnswers([answerId]);
        }
    };

    const handleSubmitAnswer = () => {
        setIsLoading(true);
        const currentQuestion = currentQuiz.questions[currentQuestionIndex];
        const correctAnswers = currentQuestion.answers.filter(a => a.isCorrect).map(a => a.id);

        // Vérifier si les réponses sélectionnées sont correctes
        const isCorrect = correctAnswers.length === selectedAnswers.length &&
            correctAnswers.every(id => selectedAnswers.includes(id));

        // Calculer le score
        if (isCorrect) {
            setScore((prevScore) => prevScore + 1);
        }

        // Enregistrer les réponses de l'utilisateur
        setUserAnswers((prev) => [
            ...prev,
            {
                questionIndex: currentQuestionIndex,
                selected: selectedAnswers,
                isCorrect,
                correctAnswers,
            },
        ]);

        setTimeout(() => {
            handleNextQuestion();
            setIsLoading(false);
            setSelectedAnswers([]);
        }, 500);
    };

    const handleNextQuestion = () => {
        if (currentQuestionIndex < currentQuiz.questions.length - 1) {
            const nextQuestionIndex = currentQuestionIndex + 1;
            setCurrentQuestionIndex(nextQuestionIndex);

            // Mettre à jour timeLeft avec le timeLimit de la prochaine question
            const nextTimeLimit = currentQuiz.questions[nextQuestionIndex]?.timeLimit || 15;
            setTimeLeft(nextTimeLimit);
        } else {
            setIsQuizStarted(false);
            setQuizFinished(true);
            // Marquer le quiz comme terminé
            // Note: Le marquage comme complété est géré lors de la soumission ou de la fin du quiz
        }
    };

    const closeQuiz = () => {
        setShowResults(false);
        // Revenir à la liste des quiz
        setCurrentQuiz(null);
        setQuizFinished(false);
    };

    const submitScore = () => {
        if (isUserLoggedIn) {
            // Soumettre le score via l'API
            fetch(`/api/quiz/${currentQuiz.id}/submit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ score }),
            })
                .then(response => response.json())
                .then(data => {
                    // Marquer le quiz comme complété
                    setCompletedQuizzes(prev => [...prev, currentQuiz.id]);
                    // Afficher un message de succès
                    alert('Score soumis avec succès !');
                    // Revenir à la liste des quiz
                    closeQuiz();
                })
                .catch(error => {
                    console.error('Erreur lors de la soumission du score :', error);
                });
        } else {
            // L'utilisateur n'est pas connecté
            alert('Veuillez vous connecter pour soumettre votre score.');
        }
    };

    const completeQuizWithoutSubmitting = () => {
        if (isUserLoggedIn) {
            // Appeler l'API pour marquer le quiz comme complété sans soumettre le score
            fetch(`/api/quiz/${currentQuiz.id}/complete`, {
                method: 'POST',
            })
                .then(response => response.json())
                .then(data => {
                    // Marquer le quiz comme complété
                    setCompletedQuizzes(prev => [...prev, currentQuiz.id]);
                    // Revenir à la liste des quiz
                    closeQuiz();
                })
                .catch(error => {
                    console.error('Erreur lors du marquage du quiz comme complété :', error);
                });
        } else {
            // Stocker l'ID du quiz dans localStorage
            const storedCompletedQuizzes = JSON.parse(localStorage.getItem('completedQuizzes')) || [];
            const updatedCompletedQuizzes = [...storedCompletedQuizzes, currentQuiz.id];
            localStorage.setItem('completedQuizzes', JSON.stringify(updatedCompletedQuizzes));
            setCompletedQuizzes(updatedCompletedQuizzes);
            // Revenir à la liste des quiz
            closeQuiz();
        }
    };

    const redirectToSignup = () => {
        // Rediriger l'utilisateur vers la page d'inscription
        window.location.href = '/signup';
    };

    if (quizzes === undefined) {
        // Afficher le chargement initial
        return <p className="text-center text-gray-500">Chargement des quiz...</p>;
    }

    if (quizzes === null || quizzes.length === 0) {
        // Si aucun quiz n'existe, ne rien afficher
        return null;
    }

    if (currentQuiz) {
        // Afficher le quiz sélectionné
        const currentQuestion = currentQuiz.questions[currentQuestionIndex];
        const isMultipleChoice = currentQuestion.type === "multiple_choice";

        return (
            <div className="mt-10 flex flex-col items-center justify-center">
                <div className="w-full py-4 px-4 border border-slate-200 shadow rounded-md bg-white dark:bg-primary-950">
                    <h1 className="text-2xl font-medium text-center mb-2">
                        {currentQuiz.title}
                    </h1>

                    {quizFinished ? (
                        showResults ? (
                            <div className="text-center">
                                <p className="text-lg font-medium mb-4">Quiz terminé !</p>
                                <p className="text-lg mb-6">
                                    Ton score : <span className="font-bold">{score}/{currentQuiz.questions.length}</span>
                                </p>
                                <div className="mb-6 space-y-4">
                                    {currentQuiz.questions.map((question, index) => {
                                        const userAnswer = userAnswers.find(
                                            (answer) => answer.questionIndex === index
                                        );
                                        return (
                                            <div
                                                key={index}
                                                className="p-4 rounded-md bg-primary-100 dark:bg-primary-1000"
                                            >
                                                <p className="text-lg font-medium mb-2">{question.text}</p>
                                                {question.answers.map((answer) => {
                                                    const isUserSelected = userAnswer.selected.includes(answer.id);
                                                    return (
                                                        <div
                                                            key={answer.id}
                                                            className={`flex items-center space-x-2 mb-1 ${
                                                                answer.isCorrect
                                                                    ? "text-green-500"
                                                                    : isUserSelected
                                                                        ? "text-red-500"
                                                                        : "text-gray-500"
                                                            }`}
                                                        >
                                                            {answer.isCorrect ? (
                                                                <CheckCircle className="w-5 h-5" />
                                                            ) : isUserSelected ? (
                                                                <XCircle className="w-5 h-5" />
                                                            ) : (
                                                                <div className="w-5 h-5"></div>
                                                            )}
                                                            <span>{answer.text}</span>
                                                        </div>
                                                    );
                                                })}
                                            </div>
                                        );
                                    })}
                                </div>
                                {isUserLoggedIn ? (
                                    <>
                                        <p>Voulez-vous soumettre votre score ?</p>
                                        <div className="flex gap-2 justify-center mt-4">
                                            <button
                                                onClick={submitScore}
                                                className="btn btn-primary"
                                            >
                                                Soumettre le score
                                            </button>
                                            <button
                                                onClick={completeQuizWithoutSubmitting}
                                                className="btn btn-light"
                                            >
                                                Ne pas soumettre
                                            </button>
                                        </div>
                                    </>
                                ) : (
                                    <>
                                        <p>Créez un compte pour sauvegarder votre score et suivre votre progression !</p>
                                        <div className="flex gap-2 justify-center mt-4">
                                            <button
                                                onClick={redirectToSignup}
                                                className="btn btn-primary"
                                            >
                                                Créer un compte
                                            </button>
                                            <button
                                                onClick={completeQuizWithoutSubmitting}
                                                className="btn btn-light"
                                            >
                                                Terminer sans sauvegarder
                                            </button>
                                        </div>
                                    </>
                                )}
                            </div>
                        ) : (
                            <div className="text-center">
                                <p className="text-lg font-medium mb-4">Merci d'avoir participé au quiz !</p>
                                <button
                                    onClick={closeQuiz}
                                    className="btn btn-light"
                                >
                                    Retour à la liste des quiz
                                </button>
                            </div>
                        )
                    ) : isQuizStarted ? (
                        isLoading ? (
                            <div className="flex flex-col items-center">
                                <Loader className="w-12 h-12 animate-spin text-primary-500 dark:text-primary-300 mb-4" />
                                <p className="text-lg font-medium">Chargement...</p>
                            </div>
                        ) : (
                            <>
                                <div className="mb-4">
                                    <h2 className="text-xl text-muted font-medium mb-2">
                                        Question {currentQuestionIndex + 1}/{currentQuiz.questions.length}
                                    </h2>
                                    <p className="text-lg text-primary-900 dark:text-primary-300">
                                        {currentQuestion.text}
                                    </p>
                                </div>
                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    {currentQuestion.answers.map((answer) => {
                                        const isSelected = selectedAnswers.includes(answer.id);
                                        return (
                                            <button
                                                key={answer.id}
                                                onClick={() =>
                                                    handleAnswerSelection(answer.id)
                                                }
                                                className={`btn ${isSelected ? 'btn-primary' : 'btn-light'}`}
                                                disabled={timeLeft === 0}
                                            >
                                                {answer.text}
                                            </button>
                                        );
                                    })}
                                </div>
                                <div className="mt-6 flex items-center justify-between">
                                    <p className="text-lg font-medium flex flex-col lg:flex-row gap-1">
                                        <span><Timer />{" "}</span>
                                        <span> {timeLeft}s restantes</span>
                                    </p>
                                    <p className="text-lg font-medium flex flex-col lg:flex-row gap-1">
                                        <span>Score : {" "}</span>
                                        <span>{score}/{currentQuiz.questions.length}</span>
                                    </p>
                                </div>
                                <div className="mt-4 flex justify-end">
                                    <button
                                        onClick={handleSubmitAnswer}
                                        className="btn btn-primary"
                                        disabled={selectedAnswers.length === 0}
                                    >
                                        {currentQuestionIndex === currentQuiz.questions.length - 1 ? 'Terminer' : 'Continuer'}
                                    </button>
                                </div>
                            </>
                        )
                    ) : null}
                </div>
            </div>
        );
    }

    // Afficher la liste des quiz
    return (
        <div className="mt-10 flex flex-col items-center justify-center">
            <div className="w-full max-w-2xl">
                <h2 className="text-2xl font-medium text-center mb-6">Liste des quiz</h2>
                <div className="space-y-4">
                    {quizzes.map((quiz) => {
                        const isCompleted = completedQuizzes.includes(quiz.id);
                        return (
                            <div
                                key={quiz.id}
                                className={`p-4 border dark:border-slate-700 rounded shadow flex flex-col lg:flex-row gap-2 items-center justify-between ${
                                    isCompleted ? 'bg-gray-100 dark:bg-gray-800 opacity-50 cursor-not-allowed' : 'bg-white dark:bg-primary-950'
                                }`}
                            >
                                <div className="flex items-center">
                                    {isCompleted && <Check className="w-6 h-6 text-green-500 mr-2" />}
                                    <span className="text-lg font-medium">{quiz.title}</span>
                                </div>
                                {!isCompleted && (
                                    <button
                                        onClick={() => startQuiz(quiz)}
                                        className="btn btn-sm btn-primary"
                                    >
                                        Commencer
                                    </button>
                                )}
                            </div>
                        );
                    })}
                </div>
            </div>
        </div>
    );
};

export default Quiz;
