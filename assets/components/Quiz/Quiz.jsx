import React, { useState, useEffect } from "react";
import { CheckCircle, XCircle, Loader, Timer, Check } from "lucide-react";

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

    useEffect(() => {
        if (contentId) {
            fetch(`/api/quiz/${contentId}`)
                .then((response) => {
                    if (response.ok) {
                        return response.json();
                    } else if (response.status === 404) {
                        setQuizzes(null);
                        return null;
                    } else {
                        throw new Error('Une erreur est survenue lors de la récupération des quiz.');
                    }
                })
                .then((data) => {
                    if (data) {
                        const quizzesData = Array.isArray(data) ? data : [data];
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

        const initialTimeLimit = quiz.questions[0]?.timeLimit || 15;
        setTimeLeft(initialTimeLimit);

        // Désactiver le scroll global
        document.body.style.overflow = 'hidden';
    };

    const closeQuiz = () => {
        setShowResults(false);
        setCurrentQuiz(null);
        setQuizFinished(false);

        // Restaurer le scroll global
        document.body.style.overflow = '';
    };

    const handleAnswerSelection = (answerId) => {
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
            setSelectedAnswers([answerId]);
        }
    };

    const handleSubmitAnswer = () => {
        setIsLoading(true);
        const currentQuestion = currentQuiz.questions[currentQuestionIndex];
        const correctAnswers = currentQuestion.answers.filter(a => a.isCorrect).map(a => a.id);

        const isCorrect = correctAnswers.length === selectedAnswers.length &&
            correctAnswers.every(id => selectedAnswers.includes(id));

        if (isCorrect) {
            setScore((prevScore) => prevScore + 1);
        }

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

            const nextTimeLimit = currentQuiz.questions[nextQuestionIndex]?.timeLimit || 15;
            setTimeLeft(nextTimeLimit);
        } else {
            setIsQuizStarted(false);
            setQuizFinished(true);
        }
    };

    const submitScore = () => {
        if (isUserLoggedIn) {
            fetch(`/api/quiz/${currentQuiz.id}/submit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ score }),
            })
                .then(response => response.json())
                .then(data => {
                    setCompletedQuizzes(prev => [...prev, currentQuiz.id]);
                    alert('Score soumis avec succès !');
                    closeQuiz();
                })
                .catch(error => {
                    console.error('Erreur lors de la soumission du score :', error);
                });
        } else {
            alert('Veuillez vous connecter pour soumettre votre score.');
        }
    };

    const completeQuizWithoutSubmitting = () => {
        if (isUserLoggedIn) {
            fetch(`/api/quiz/${currentQuiz.id}/complete`, {
                method: 'POST',
            })
                .then(response => response.json())
                .then(data => {
                    setCompletedQuizzes(prev => [...prev, currentQuiz.id]);
                    closeQuiz();
                })
                .catch(error => {
                    console.error('Erreur lors du marquage du quiz comme complété :', error);
                });
        } else {
            const storedCompletedQuizzes = JSON.parse(localStorage.getItem('completedQuizzes')) || [];
            const updatedCompletedQuizzes = [...storedCompletedQuizzes, currentQuiz.id];
            localStorage.setItem('completedQuizzes', JSON.stringify(updatedCompletedQuizzes));
            setCompletedQuizzes(updatedCompletedQuizzes);
            closeQuiz();
        }
    };

    const redirectToSignup = () => {
        window.location.href = '/inscription';
    };

    if (quizzes === undefined) {
        return <p className="text-center text-gray-500">Chargement des quiz...</p>;
    }

    if (quizzes === null || quizzes.length === 0) {
        return null;
    }

    if (currentQuiz) {
        const currentQuestion = currentQuiz.questions[currentQuestionIndex];
        const isMultipleChoice = currentQuestion.type === "multiple_choice";

        // Quand le quiz est en cours, on affiche un overlay plein écran
        return (
            <div className="fixed inset-0 z-50 flex flex-col items-center justify-center bg-white dark:bg-slate-900 p-4">
                <div className="w-full max-w-3xl py-6 px-6 border border-slate-200 dark:border-slate-800 shadow-lg rounded-md">
                    <h1 className="text-2xl font-semibold text-center mb-4 whitespace-normal break-words text-gray-800 dark:text-gray-200">
                        {currentQuiz.title}
                    </h1>

                    {quizFinished ? (
                        showResults ? (
                            <div className="text-center">
                                <p className="text-xl font-medium mb-4 text-gray-800 dark:text-gray-200">Quiz terminé !</p>
                                <p className="text-lg mb-6 text-gray-700 dark:text-gray-300">
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
                                                className="p-4 rounded-md bg-slate-100 dark:bg-slate-800"
                                            >
                                                <p className="text-lg font-medium mb-2 whitespace-normal break-words text-gray-800 dark:text-gray-200 text-center">
                                                    {question.text}
                                                </p>
                                                {question.answers.map((answer) => {
                                                    const isUserSelected = userAnswer.selected.includes(answer.id);
                                                    return (
                                                        <div
                                                            key={answer.id}
                                                            className={`flex items-center gap-2 mb-1 whitespace-normal break-words ${
                                                                answer.isCorrect
                                                                    ? "text-green-500"
                                                                    : isUserSelected
                                                                        ? "text-red-500"
                                                                        : "text-gray-700 dark:text-gray-300"
                                                            }`}
                                                        >
                                                            {answer.isCorrect ? (
                                                                <CheckCircle className="w-5 h-5" />
                                                            ) : isUserSelected ? (
                                                                <XCircle className="w-5 h-5" />
                                                            ) : (
                                                                <div className="w-5 h-5"></div>
                                                            )}
                                                            <span className="whitespace-normal break-words">
                                                                {answer.text}
                                                            </span>
                                                        </div>
                                                    );
                                                })}
                                            </div>
                                        );
                                    })}
                                </div>
                                {isUserLoggedIn ? (
                                    <>
                                        <p className="text-lg text-gray-800 dark:text-gray-200">Voulez-vous soumettre votre score ?</p>
                                        <div className="flex flex-col lg:flex-row gap-2 justify-center mt-4">
                                            <button
                                                onClick={submitScore}
                                                className="px-4 py-2 font-semibold rounded-md bg-indigo-600 text-white hover:bg-indigo-700 transition-colors"
                                            >
                                                Soumettre le score
                                            </button>
                                            <button
                                                onClick={completeQuizWithoutSubmitting}
                                                className="px-4 py-2 font-semibold rounded-md bg-gray-200 dark:bg-slate-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-slate-600 transition-colors"
                                            >
                                                Ne pas soumettre
                                            </button>
                                        </div>
                                    </>
                                ) : (
                                    <>
                                        <p className="text-lg text-gray-800 dark:text-gray-200">Créez un compte pour sauvegarder votre score et suivre votre progression !</p>
                                        <div className="flex flex-col lg:flex-row gap-2 justify-center mt-4">
                                            <button
                                                onClick={redirectToSignup}
                                                className="px-4 py-2 font-semibold rounded-md bg-indigo-600 text-white hover:bg-indigo-700 transition-colors"
                                            >
                                                Créer un compte
                                            </button>
                                            <button
                                                onClick={completeQuizWithoutSubmitting}
                                                className="px-4 py-2 font-semibold rounded-md bg-gray-200 dark:bg-slate-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-slate-600 transition-colors"
                                            >
                                                Terminer sans sauvegarder
                                            </button>
                                        </div>
                                    </>
                                )}
                            </div>
                        ) : (
                            <div className="text-center">
                                <p className="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Merci d'avoir participé au quiz !</p>
                                <button
                                    onClick={closeQuiz}
                                    className="px-4 py-2 font-semibold rounded-md bg-gray-200 dark:bg-slate-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-slate-600 transition-colors"
                                >
                                    Retour à la liste des quiz
                                </button>
                            </div>
                        )
                    ) : isQuizStarted ? (
                        isLoading ? (
                            <div className="flex flex-col items-center">
                                <Loader className="w-12 h-12 animate-spin text-indigo-500 dark:text-indigo-300 mb-4" />
                                <p className="text-lg font-medium text-gray-800 dark:text-gray-200">Chargement...</p>
                            </div>
                        ) : (
                            <>
                                <div className="mb-4">
                                    <h2 className="text-xl text-gray-700 dark:text-gray-300 font-medium mb-2 text-center">
                                        Question {currentQuestionIndex + 1}/{currentQuiz.questions.length}
                                    </h2>
                                    <p className="text-lg text-gray-800 dark:text-gray-200 whitespace-normal break-words w-full text-center">
                                        {currentQuestion.text}
                                    </p>
                                </div>
                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    {currentQuestion.answers.map((answer) => {
                                        const isSelected = selectedAnswers.includes(answer.id);
                                        const isDisabled = timeLeft === 0;

                                        return (
                                            <div
                                                key={answer.id}
                                                onClick={() => !isDisabled && handleAnswerSelection(answer.id)}
                                                className={`
                                                    w-full p-3 flex items-center justify-center text-center rounded-md shadow-sm 
                                                    whitespace-normal break-words transition-colors
                                                    border border-slate-200 dark:border-slate-700
                                                    ${isSelected ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-slate-800 text-gray-800 dark:text-gray-200'}
                                                    ${!isDisabled ? 'cursor-pointer hover:bg-indigo-100 dark:hover:bg-indigo-900' : 'opacity-50 cursor-not-allowed'}
                                                `}
                                                role="button"
                                                tabIndex={0}
                                                onKeyDown={(e) => {
                                                    if (!isDisabled && (e.key === 'Enter' || e.key === ' ')) {
                                                        handleAnswerSelection(answer.id);
                                                    }
                                                }}
                                            >
                                                {answer.text}
                                            </div>
                                        );
                                    })}
                                </div>
                                <div className="mt-6 flex items-center justify-between text-gray-700 dark:text-gray-300">
                                    <p className="text-lg font-medium flex flex-col lg:flex-row gap-1">
                                        <span><Timer />{" "}</span>
                                        <span>{timeLeft}s restantes</span>
                                    </p>
                                    <p className="text-lg font-medium flex flex-col lg:flex-row gap-1">
                                        <span>Score : </span>
                                        <span>{score}/{currentQuiz.questions.length}</span>
                                    </p>
                                </div>
                                <div className="mt-4 flex justify-end">
                                    <button
                                        onClick={handleSubmitAnswer}
                                        className="px-4 py-2 font-semibold rounded-md bg-indigo-600 text-white hover:bg-indigo-700 transition-colors"
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

    // Afficher la liste des quiz si aucun quiz en cours
    return (
        <div className="mt-10 flex flex-col items-center justify-center px-4">
            <div className="w-full max-w-2xl">
                <h2 className="text-2xl font-medium text-center mb-1 text-gray-700 dark:text-gray-200">Prêt à tester vos connaissances ?</h2>
                <p className="text-lg text-center text-gray-600 dark:text-gray-400 mb-6">Choisissez un quiz parmi la liste ci-dessous pour commencer.</p>
                <div className="space-y-4">
                    {quizzes.map((quiz) => {
                        const isCompleted = completedQuizzes.includes(quiz.id);
                        return (
                            <div
                                key={quiz.id}
                                className={`p-4 border dark:border-slate-700 rounded shadow flex flex-col lg:flex-row gap-2 items-center justify-between ${
                                    isCompleted ? 'bg-gray-100 dark:bg-gray-800 opacity-50 cursor-not-allowed' : 'bg-white dark:bg-slate-900'
                                }`}
                            >
                                <div className="flex items-center whitespace-normal break-words max-w-[350px] text-gray-800 dark:text-gray-200">
                                    {isCompleted && <Check className="w-6 h-6 text-green-500 mr-2" />}
                                    <span className="text-lg font-medium text-center lg:text-left">
                                        {quiz.title}
                                    </span>
                                </div>
                                {!isCompleted && (
                                    <button
                                        onClick={() => startQuiz(quiz)}
                                        className="px-3 py-2 text-sm font-semibold rounded-md bg-indigo-600 text-white hover:bg-indigo-700 transition-colors"
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
