import React from 'react';
import { CheckCircle, XCircle } from "lucide-react";

const QuizResults = ({
                         currentQuiz,
                         userAnswers,
                         score,
                         isUserLoggedIn,
                         submitScore,
                         completeQuizWithoutSubmitting,
                         closeQuiz,
                         redirectToSignup
                     }) => {
    const totalQuestions = currentQuiz.questions.length;
    const ratio = score / totalQuestions;
    let feedbackMessage = "Recommencez pour vous entraîner";
    if (ratio >= 0.8) {
        feedbackMessage = "Excellent travail !";
    } else if (ratio >= 0.5) {
        feedbackMessage = "Vous pouvez vous améliorer";
    }

    return (
        <div className="text-center">
            <p className="text-2xl font-semibold mb-4 text-gray-800 dark:text-gray-100">Quiz terminé !</p>
            <p className="text-xl mb-6 text-gray-700 dark:text-gray-300">
                Ton score : <span className="font-bold">{score}/{totalQuestions}</span>
            </p>
            <p className="text-lg mb-8 text-gray-800 dark:text-gray-200 font-semibold">
                {feedbackMessage}
            </p>

            <div className="space-y-6 mb-8">
                {currentQuiz.questions.map((question, index) => {
                    const userAnswer = userAnswers.find((answer) => answer.questionIndex === index);
                    const userSelectedAnswers = userAnswer?.selected || [];

                    return (
                        <div
                            key={index}
                            className="p-4 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-left"
                        >
                            <p className="text-lg font-medium mb-3 text-gray-800 dark:text-gray-100">
                                {question.text}
                            </p>
                            <div className="space-y-2">
                                {question.answers.map((answer) => {
                                    const isUserSelected = userSelectedAnswers.includes(answer.id);
                                    const isCorrect = answer.isCorrect;
                                    let answerClasses = "flex items-start gap-2 p-2 rounded";
                                    if (isCorrect) {
                                        answerClasses += " bg-green-50 dark:bg-green-900 text-green-700 dark:text-green-200";
                                    } else if (isUserSelected && !isCorrect) {
                                        answerClasses += " bg-red-50 dark:bg-red-900 text-red-700 dark:text-red-200";
                                    } else {
                                        answerClasses += " bg-gray-100 dark:bg-slate-700 text-gray-800 dark:text-gray-200";
                                    }

                                    return (
                                        <div key={answer.id} className={answerClasses}>
                                            {isCorrect ? (
                                                <CheckCircle className="w-5 h-5 flex-shrink-0" />
                                            ) : isUserSelected ? (
                                                <XCircle className="w-5 h-5 flex-shrink-0" />
                                            ) : (
                                                <div className="w-5 h-5 flex-shrink-0"></div>
                                            )}
                                            <span className="break-words flex-1">{answer.text}</span>
                                        </div>
                                    );
                                })}
                                {userSelectedAnswers.length === 0 && (
                                    <div className="text-sm text-gray-500 dark:text-gray-400 italic">
                                        Aucune réponse sélectionnée
                                    </div>
                                )}
                            </div>
                        </div>
                    );
                })}
            </div>

            {isUserLoggedIn ? (
                <>
                    <p className="text-lg text-gray-800 dark:text-gray-200 mb-4">Voulez-vous soumettre votre score ?</p>
                    <div className="flex flex-col sm:flex-row gap-2 justify-center">
                        <button
                            onClick={submitScore}
                            className="btn btn-primary"
                            aria-label="Enregistrer mon score"
                        >
                            Enregistrer mon score
                        </button>
                        <button
                            onClick={completeQuizWithoutSubmitting}
                            className="btn btn-light"
                            aria-label="J'ai fini"
                        >
                            Terminer
                        </button>
                    </div>
                </>
            ) : (
                <>
                    <p className="text-lg text-gray-800 dark:text-gray-200 mb-4">Créez un compte pour sauvegarder votre score et suivre votre progression !</p>
                    <div className="flex flex-col sm:flex-row gap-2 justify-center">
                        <button
                            onClick={redirectToSignup}
                            className="btn btn-primary"
                            aria-label="Créer un compte"
                        >
                            Créer un compte
                        </button>
                        <button
                            onClick={completeQuizWithoutSubmitting}
                            className="btn btn-light"
                            aria-label="Terminer"
                        >
                            Terminer
                        </button>
                    </div>
                </>
            )}

            <div className="mt-8">
                <button
                    onClick={closeQuiz}
                    className="btn btn-light"
                    aria-label="Recommencer"
                >
                    Recommencer
                </button>
            </div>
        </div>
    );
};

export default QuizResults;