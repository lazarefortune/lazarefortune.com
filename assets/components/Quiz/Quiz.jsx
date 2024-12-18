import React, { useState, useEffect } from "react";
import { Loader, Check } from "lucide-react";
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

    useEffect(() => {
        if (contentId) {
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
        setShowImmediateFeedback(false);
        setIsCorrectFeedback(null);
        setAnswerSubmitted(false);

        const initialTimeLimit = quiz.questions[0]?.timeLimit || 15;
        setTimeLeft(initialTimeLimit);

        document.body.style.overflow = 'hidden';
    };

    const closeQuiz = () => {
        setShowResults(false);
        setCurrentQuiz(null);
        setQuizFinished(false);
        document.body.style.overflow = '';
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

        setShowImmediateFeedback(true);
        setIsCorrectFeedback(isCorrect);
        setAnswerSubmitted(true);
        setIsLoading(false);
    };

    const handleNextQuestion = () => {
        setShowImmediateFeedback(false);
        setIsCorrectFeedback(null);
        setAnswerSubmitted(false);

        if (currentQuestionIndex < currentQuiz.questions.length - 1) {
            const nextQuestionIndex = currentQuestionIndex + 1;
            setCurrentQuestionIndex(nextQuestionIndex);
            const nextTimeLimit = currentQuiz.questions[nextQuestionIndex]?.timeLimit || 15;
            setTimeLeft(nextTimeLimit);
            setSelectedAnswers([]);
        } else {
            setIsQuizStarted(false);
            setQuizFinished(true);
        }
    };

    const handleSkipQuestion = () => {
        // Pas de scoring, pas de feedback, direct passage à la prochaine question
        setUserAnswers((prev) => [
            ...prev,
            {
                questionIndex: currentQuestionIndex,
                selected: [],
                isCorrect: false,
                correctAnswers: currentQuiz.questions[currentQuestionIndex].answers
                    .filter(a => a.isCorrect)
                    .map(a => a.id),
            },
        ]);
        handleNextQuestion();
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
                .then(() => {
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
                .then(() => {
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
        return <p className="text-center text-gray-500 mt-10">Chargement des quiz...</p>;
    }

    if (quizzes === null || quizzes.length === 0) {
        return null;
    }

    if (currentQuiz) {
        if (quizFinished) {
            return (
                <div className="fixed inset-0 z-50 bg-white dark:bg-slate-900 p-4 overflow-auto">
                    <div className="w-full max-w-3xl mx-auto py-6 px-6 border border-slate-200 dark:border-slate-800 shadow-lg rounded bg-white dark:bg-slate-900">
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
                                <p className="text-lg font-medium mb-4 text-gray-800 dark:text-gray-200">Merci d'avoir participé au quiz !</p>
                                <button
                                    onClick={closeQuiz}
                                    className="px-4 py-2 font-semibold rounded bg-gray-200 dark:bg-slate-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-slate-600 transition-colors"
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
            <div className="fixed inset-0 z-50 bg-white dark:bg-slate-900 p-4 overflow-auto">
                <div className="w-full max-w-3xl mx-auto py-6 px-6 border border-slate-200 dark:border-slate-800 shadow-lg rounded bg-white dark:bg-slate-900">
                    {isLoading ? (
                        <div className="flex flex-col items-center">
                            <Loader className="w-12 h-12 animate-spin text-primary-500 dark:text-primary-300 mb-4" />
                            <p className="text-lg font-medium text-gray-800 dark:text-gray-200">Chargement...</p>
                        </div>
                    ) : (
                        <Question
                            currentQuestion={currentQuestion}
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

    // Liste des quiz
    return (
        <div className="mt-10 flex flex-col items-center justify-start">
            <div className="w-full max-w-2xl">
                <h2 className="text-2xl font-medium text-center mb-4 text-gray-700 dark:text-gray-200">Prêt à tester vos connaissances ?</h2>
                <div className="space-y-4">
                    {quizzes.map((quiz) => {
                        const isCompleted = completedQuizzes.includes(quiz.id);
                        return (
                            <div
                                key={quiz.id}
                                className={`p-4 border dark:border-slate-700 rounded shadow flex flex-col lg:flex-row gap-2 items-center justify-between transition-all duration-300
                                    ${isCompleted ? 'bg-gray-100 dark:bg-gray-800 opacity-50 cursor-not-allowed' : 'bg-white dark:bg-slate-900 hover:shadow-lg'}
                                `}
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
                                        className="btn btn-primary"
                                        aria-label={`Commencer le quiz ${quiz.title}`}
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
