import React, { useState, useEffect } from "react";
import { CheckCircle, XCircle, Loader } from "lucide-react";

const Quiz = () => {
    const [quiz, setQuiz] = useState([]);
    const [currentQuestionIndex, setCurrentQuestionIndex] = useState(0);
    const [score, setScore] = useState(0);
    const [timeLeft, setTimeLeft] = useState(10);
    const [isQuizStarted, setIsQuizStarted] = useState(false);
    const [userAnswers, setUserAnswers] = useState([]);
    const [quizFinished, setQuizFinished] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const [showResults, setShowResults] = useState(true);

    useEffect(() => {
        fetch("/api/quiz")
            .then((response) => response.json())
            .then((data) => setQuiz(data));
    }, []);

    useEffect(() => {
        if (isQuizStarted && timeLeft > 0 && !isLoading) {
            const timer = setTimeout(() => setTimeLeft(timeLeft - 1), 1000);
            return () => clearTimeout(timer);
        } else if (timeLeft === 0) {
            handleNextQuestion();
        }
    }, [timeLeft, isQuizStarted, isLoading]);

    const startQuiz = () => {
        setIsQuizStarted(true);
        setCurrentQuestionIndex(0);
        setTimeLeft(10);
        setQuizFinished(false);
        setScore(0);
        setUserAnswers([]);
        setIsLoading(false);
        setShowResults(true);
    };

    const handleAnswer = (isCorrect, optionId, question) => {
        setIsLoading(true);
        setUserAnswers((prev) => [
            ...prev,
            { questionId: question.id, selected: optionId, isCorrect },
        ]);
        if (isCorrect) {
            setScore((prevScore) => prevScore + 1);
        }
        setTimeout(() => {
            handleNextQuestion();
            setIsLoading(false);
        }, 500);
    };

    const handleNextQuestion = () => {
        setTimeLeft(10);
        if (currentQuestionIndex < quiz.length - 1) {
            setCurrentQuestionIndex((prevIndex) => prevIndex + 1);
        } else {
            setIsQuizStarted(false);
            setQuizFinished(true);
        }
    };

    const closeQuiz = () => {
        setShowResults(false);
    };

    if (!quiz.length) {
        return <p className="text-center text-gray-500">Chargement des questions...</p>;
    }

    const currentQuestion = quiz[currentQuestionIndex];

    return (
        <div className="mt-4 flex flex-col items-center justify-center">
            <div className="w-full py-4 px-4 border border-slate-200 shadow shadow-slate-400 dark:border-slate-700 rounded-md
            bg-white dark:bg-primary-950">
                <h1 className="text-2xl font-medium text-center text-leading mb-2">
                    {isQuizStarted ? "Quiz en cours" : "Un quiz pour toi"}
                </h1>

                {quizFinished ? (
                    showResults ? (
                        <div className="text-center">
                            <p className="text-lg font-medium mb-4">Quiz terminé !</p>
                            <p className="text-lg mb-6">
                                Ton score : <span className="font-bold">{score}/{quiz.length}</span>
                            </p>
                            <div className="mb-6 space-y-4">
                                {quiz.map((question, index) => {
                                    const userAnswer = userAnswers.find(
                                        (answer) => answer.questionId === question.id
                                    );
                                    return (
                                        <div
                                            key={index}
                                            className="p-4 rounded-md bg-primary-100 dark:bg-primary-1000"
                                        >
                                            <p className="font-semibold mb-2">{question.question}</p>
                                            {question.options.map((option) => (
                                                <div
                                                    key={option.id}
                                                    className={`flex items-center space-x-2 mb-1 ${
                                                        option.isCorrect
                                                            ? "text-green-500"
                                                            : userAnswer?.selected === option.id
                                                                ? "text-red-500"
                                                                : "text-gray-500"
                                                    }`}
                                                >
                                                    {option.isCorrect ? (
                                                        <CheckCircle className="w-5 h-5" />
                                                    ) : userAnswer?.selected === option.id ? (
                                                        <XCircle className="w-5 h-5" />
                                                    ) : (
                                                        <div className="w-5 h-5"></div>
                                                    )}
                                                    <span>{option.text}</span>
                                                </div>
                                            ))}
                                        </div>
                                    );
                                })}
                            </div>
                            <div className="flex gap-2">
                                <button
                                    onClick={startQuiz}
                                    className="btn btn-primary mr-4"
                                >
                                    Recommencer
                                </button>
                                <button
                                    onClick={closeQuiz}
                                    className="btn btn-light"
                                >
                                    Terminer
                                </button>
                            </div>
                        </div>
                    ) : (
                        <div className="text-center">
                            <p className="text-lg font-medium mb-4">Merci d'avoir participé au quiz !</p>
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
                                <h2 className="text-xl font-medium mb-2">
                                    Question {currentQuestionIndex + 1}/{quiz.length}
                                </h2>
                                <p className="text-primary-900 dark:text-primary-300">
                                    {currentQuestion.question}
                                </p>
                            </div>
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {currentQuestion.options.map((option) => (
                                    <button
                                        key={option.id}
                                        onClick={() =>
                                            handleAnswer(option.isCorrect, option.id, currentQuestion)
                                        }
                                        className="btn btn-light"
                                        disabled={timeLeft === 0}
                                    >
                                        {option.text}
                                    </button>
                                ))}
                            </div>
                            <div className="mt-6 flex items-center justify-between">
                                <p className="text-lg font-medium flex flex-col lg:flex-row gap-1">
                                    <span>Temps restant : {" "}</span>
                                    <span> {timeLeft} secondes</span>
                                </p>
                                <p className="text-lg font-medium flex flex-col lg:flex-row gap-1">
                                    <span>Score : {" "}</span>
                                    <span>{score}/{quiz.length}</span>
                                </p>
                            </div>
                        </>
                    )
                ) : (
                    <div className="text-center">
                        <p className="text-muted mb-4">
                            Prêt à commencer le quiz ?
                        </p>
                        <button
                            onClick={startQuiz}
                            className="btn btn-primary"
                        >
                            Commencer
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
};

export default Quiz;
