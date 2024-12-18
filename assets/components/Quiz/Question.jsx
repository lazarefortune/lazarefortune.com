import React, { useEffect, useRef } from 'react';
import { Timer, ListChecks, CircleDot } from 'lucide-react';

function formatTime(totalSeconds) {
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    if (hours > 0) {
        // Format HH:MM:SS
        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    } else {
        // Format MM:SS
        return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    }
}

const Question = ({
                      currentQuestion,
                      currentQuestionIndex,
                      questionCount,
                      selectedAnswers,
                      handleAnswerSelection,
                      handleSubmitAnswer,
                      handleSkipQuestion,
                      timeLeft,
                      score,
                      isCorrectFeedback,
                      showImmediateFeedback,
                      isMultipleChoice,
                      handleNextQuestion,
                      answerSubmitted
                  }) => {
    const questionRef = useRef(null);

    useEffect(() => {
        if (questionRef.current) {
            questionRef.current.focus();
        }
    }, [currentQuestionIndex]);

    const progressPercentage = ((currentQuestionIndex + 1) / questionCount) * 100;

    const totalTime = currentQuestion?.timeLimit || 15;
    const timeRatio = timeLeft / totalTime;

    let strokeColor = "stroke-blue-500";
    if (timeRatio <= 0.2) {
        strokeColor = "stroke-red-500";
    } else if (timeRatio <= 0.4) {
        strokeColor = "stroke-orange-500";
    }

    // On agrandit légèrement le cercle
    const radius = 45;
    const circumference = 2 * Math.PI * radius;
    const offset = circumference - (timeRatio * circumference);

    const QuestionIcon = isMultipleChoice ? ListChecks : CircleDot;

    let feedbackMessage = "";
    if (showImmediateFeedback && isCorrectFeedback !== null) {
        feedbackMessage = isCorrectFeedback ? "Bonne réponse !" : "Mauvaise réponse...";
    }

    const instructionText = isMultipleChoice
        ? "Vous pouvez sélectionner plusieurs réponses."
        : "Sélectionnez une réponse.";

    return (
        <div>
            {/* Barre de progression du quiz */}
            <div className="mb-6">
                <div className="flex items-center justify-between mb-2">
                    <span className="text-sm font-medium text-gray-700 dark:text-gray-300">Progression</span>
                    <span className="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {currentQuestionIndex + 1}/{questionCount}
                    </span>
                </div>
                <div className="w-full bg-gray-200 rounded h-2 dark:bg-gray-700 transition-all">
                    <div
                        className="bg-blue-600 h-2 rounded transition-all duration-500"
                        style={{width: `${progressPercentage}%`}}
                    />
                </div>
            </div>

            {/* Titre de la question */}
            <p
                className="text-xl text-gray-800 dark:text-gray-100 whitespace-normal break-words w-full font-semibold mb-4 focus:outline-none"
                tabIndex={-1}
                ref={questionRef}
            >
                {currentQuestion.text}
            </p>

            {/* Icône type de question et instructions */}
            <div className="flex items-center gap-2 mb-4">
                <QuestionIcon className="w-5 h-5 text-gray-700 dark:text-gray-300" />
                <span className="text-sm text-gray-600 dark:text-gray-400">{instructionText}</span>
            </div>

            {/* Réponses */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                {currentQuestion.answers.map((answer) => {
                    const isSelected = selectedAnswers.includes(answer.id);

                    let answerClasses = `
                        w-full p-3 flex items-center justify-center text-center rounded shadow 
                        whitespace-normal break-words transition-all duration-300
                        border border-slate-200 dark:border-slate-700 cursor-pointer
                    `;

                    if (answerSubmitted) {
                        if (answer.isCorrect) {
                            answerClasses += " bg-green-100 text-green-700 dark:bg-green-800 dark:text-green-200";
                        } else if (isSelected && !answer.isCorrect) {
                            answerClasses += " bg-red-100 text-red-700 dark:bg-red-800 dark:text-red-200";
                        } else {
                            answerClasses += " bg-white dark:bg-slate-800 text-gray-800 dark:text-gray-200";
                        }
                    } else {
                        if (isSelected) {
                            answerClasses += " bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100";
                        } else {
                            answerClasses += " bg-white dark:bg-slate-800 text-gray-800 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900";
                        }
                    }

                    const isDisabled = answerSubmitted || timeLeft === 0;

                    return (
                        <div
                            key={answer.id}
                            onClick={() => !isDisabled && handleAnswerSelection(answer.id)}
                            className={answerClasses + " rounded"}
                            role="button"
                            tabIndex={0}
                            aria-label={`Réponse : ${answer.text}`}
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

            {/* Bloc bas : timer + score + actions */}
            <div className="flex flex-col lg:flex-row items-center justify-between gap-4 mt-6">

                {/* Timer circulaire */}
                <div className="relative flex items-center justify-center w-16 h-16">
                    <svg className="transform -rotate-90 absolute w-full h-full" viewBox="0 0 100 100">
                        <circle
                            cx="50"
                            cy="50"
                            r={radius}
                            strokeWidth="10"
                            strokeDasharray={circumference}
                            strokeDashoffset={offset}
                            className={`transition-all duration-500 ${strokeColor}`}
                            fill="transparent"
                        />
                    </svg>
                    <div className="flex flex-col items-center justify-center z-10 text-gray-800 dark:text-gray-200">
                        <Timer className="w-4 h-4 mb-1" />
                        <span className="text-xs font-bold">{formatTime(timeLeft)}</span>
                    </div>
                </div>

                {/* Score */}
                <p className="text-lg font-medium text-gray-700 dark:text-gray-300">
                    Score : {score}/{questionCount}
                </p>
            </div>

            {/* Feedback après soumission */}
            {answerSubmitted && (
                <p className={`text-center text-lg font-bold mt-6 ${isCorrectFeedback ? 'text-green-600' : 'text-red-600'}`}>
                    {feedbackMessage}
                </p>
            )}

            {/* Boutons d'action */}
            <div className="mt-8 flex flex-col sm:flex-row gap-3 justify-end">
                {!answerSubmitted && timeLeft > 0 && (
                    <button
                        onClick={handleSkipQuestion}
                        className="btn btn-light"
                        aria-label="Passer cette question"
                    >
                        Passer
                    </button>
                )}
                {!answerSubmitted ? (
                    <button
                        onClick={handleSubmitAnswer}
                        className="btn btn-primary"
                        disabled={selectedAnswers.length === 0}
                        aria-label="Soumettre la réponse"
                    >
                        Soumettre
                    </button>
                ) : (
                    <button
                        onClick={handleNextQuestion}
                        className="btn btn-primary"
                        aria-label="Continuer à la question suivante"
                    >
                        Continuer
                    </button>
                )}
            </div>
        </div>
    );
};

export default Question;
