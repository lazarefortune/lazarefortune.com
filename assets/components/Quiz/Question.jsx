import React, { useEffect, useRef } from 'react';
import { Timer, ListChecks, CircleDot } from 'lucide-react';

function formatTime(totalSeconds) {
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    if (hours > 0) {
        // Format HH:MM:SS
        return `${hours.toString().padStart(2, '0')}:${minutes
            .toString()
            .padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    } else {
        // Format MM:SS
        return `${minutes.toString().padStart(2, '0')}:${seconds
            .toString()
            .padStart(2, '0')}`;
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

    let strokeColor = 'stroke-blue-500';
    if (timeRatio <= 0.2) {
        strokeColor = 'stroke-red-500';
    } else if (timeRatio <= 0.4) {
        strokeColor = 'stroke-orange-500';
    }

    const radius = 45;
    const circumference = 2 * Math.PI * radius;
    const offset = circumference - timeRatio * circumference;

    const QuestionIcon = isMultipleChoice ? ListChecks : CircleDot;

    let feedbackMessage = '';
    if (showImmediateFeedback && isCorrectFeedback !== null) {
        feedbackMessage = isCorrectFeedback ? 'Bonne réponse !' : 'Mauvaise réponse...';
    }

    const instructionText = isMultipleChoice
        ? 'Vous pouvez sélectionner plusieurs réponses.'
        : 'Sélectionnez une réponse.';

    // Pour afficher des lettres (A, B, C, …)
    const letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P'];

    return (
        <div>
            {/* Barre de progression du quiz */}
            <div className="mb-6">
                <div className="flex items-center justify-between mb-2">
          <span className="text-sm font-medium text-gray-700 dark:text-gray-300">
            Progression
          </span>
                    <span className="text-sm font-medium text-gray-700 dark:text-gray-300">
            {currentQuestionIndex + 1}/{questionCount}
          </span>
                </div>
                <div className="w-full bg-gray-200 rounded h-2 dark:bg-gray-700 transition-all">
                    <div
                        className="bg-blue-600 h-2 rounded transition-all duration-500"
                        style={{ width: `${progressPercentage}%` }}
                    />
                </div>
            </div>

            {/* Titre de la question */}
            <p
                className="text-xl text-gray-800 dark:text-gray-100 whitespace-normal break-words w-full font-medium mb-4 focus:outline-none"
                tabIndex={-1}
                ref={questionRef}
            >
                {currentQuestion.text}
            </p>

            {/* Icône type de question et instructions */}
            <div className="flex items-center gap-2 mb-4">
                <QuestionIcon className="w-5 h-5 text-gray-700 dark:text-gray-300" />
                <span className="text-sm text-gray-600 dark:text-gray-400">
          {instructionText}
        </span>
            </div>

            {/* Réponses */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                {currentQuestion.answers.map((answer, index) => {
                    const isSelected = selectedAnswers.includes(answer.id);
                    const isDisabled = answerSubmitted || timeLeft === 0;

                    // BORDURE autour de la réponse complète
                    let wrapperClasses =
                        'flex items-center border-2 rounded p-3 transition-all duration-300 relative cursor-pointer';

                    // Carré de la lettre (A, B, C…) - on va le personnaliser
                    let letterBlockClasses =
                        'mr-3 px-2 py-1 border-2 rounded-tl rounded-tr rounded-bl';

                    // Couleurs après soumission
                    if (answerSubmitted) {
                        if (answer.isCorrect) {
                            // Bonne réponse
                            wrapperClasses += ' border-green-600';
                            letterBlockClasses +=
                                ' border-green-600 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-200';
                        } else if (isSelected && !answer.isCorrect) {
                            // Mauvaise réponse sélectionnée
                            wrapperClasses += ' border-red-600';
                            letterBlockClasses +=
                                ' border-red-600 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-200';
                        } else {
                            // Ni bon ni sélectionné => neutre
                            wrapperClasses += ' border-gray-300 dark:border-slate-700';
                            letterBlockClasses +=
                                ' border-gray-300 dark:border-slate-700 bg-gray-100 dark:bg-slate-700 text-gray-800 dark:text-gray-200';
                        }
                    } else {
                        // Pas encore soumis
                        if (isSelected) {
                            // Sélectionné (mais pas encore validé)
                            wrapperClasses += ' border-blue-600';
                            letterBlockClasses +=
                                ' border-blue-600 bg-blue-100 dark:bg-blue-800 text-blue-700 dark:text-blue-100';
                        } else {
                            // Non sélectionné
                            wrapperClasses += ' border-gray-300 dark:border-slate-700 hover:border-blue-600';
                            letterBlockClasses +=
                                ' border-gray-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-gray-800 dark:text-gray-200';
                        }
                    }

                    return (
                        <div
                            key={answer.id}
                            onClick={() => !isDisabled && handleAnswerSelection(answer.id)}
                            className={wrapperClasses}
                            role="button"
                            tabIndex={0}
                            aria-label={`Réponse : ${answer.text}`}
                            onKeyDown={(e) => {
                                if (!isDisabled && (e.key === 'Enter' || e.key === ' ')) {
                                    handleAnswerSelection(answer.id);
                                }
                            }}
                        >
                            {/* Étiquette (A, B, C…) */}
                            <div className={letterBlockClasses}>
                                {letters[index]}
                            </div>
                            {/* Texte de la réponse */}
                            <div className="break-words text-gray-800 dark:text-gray-200">
                                {answer.text}
                            </div>
                        </div>
                    );
                })}
            </div>

            {/* Bloc bas : timer + score + actions */}
            <div className="flex flex-col lg:flex-row items-center justify-between gap-4 mt-6">
                {/* Timer circulaire */}
                <div className="relative flex items-center justify-center w-16 h-16">
                    <svg
                        className="transform -rotate-90 absolute w-full h-full"
                        viewBox="0 0 100 100"
                    >
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
            {answerSubmitted && feedbackMessage && (
                <div className="mt-2 flex justify-center">
                    <div
                        className={`
                        px-2 py-1 rounded border text-center w-full max-w-56 text-sm
                        ${
                                            isCorrectFeedback
                                                ? 
                                                'border-green-400 bg-green-50 dark:bg-green-900 text-green-700 dark:text-green-200'
                                                : 
                                                'border-red-400 bg-red-50 dark:bg-red-900 text-red-700 dark:text-red-200'
                                        }
                      `}
                    >
                        {feedbackMessage}
                    </div>
                </div>
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
