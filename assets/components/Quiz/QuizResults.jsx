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
            <p className="text-2xl font-semibold mb-4 text-gray-800 dark:text-gray-100">
                Quiz terminé !
            </p>
            <p className="text-xl mb-6 text-gray-700 dark:text-gray-300">
                Ton score : <span className="font-bold">{score}/{totalQuestions}</span>
            </p>
            <p className="text-lg mb-8 text-gray-800 dark:text-gray-200 font-semibold">
                {feedbackMessage}
            </p>

            <div className="space-y-6 mb-8">
                {currentQuiz.questions.map((question, index) => {
                    const userAnswer = userAnswers.find(
                        (answer) => answer.questionIndex === index
                    );
                    const userSelectedAnswers = userAnswer?.selected || [];
                    // Indique si la question est globalement “réussie” ou “ratée” (pour un badge)
                    const questionIsCorrect = userAnswer?.isCorrect ?? false;

                    // Pour les lettres (A, B, C…)
                    const letters = ['A','B','C','D','E','F','G','H','I','J','K','L'];

                    return (
                        <div
                            key={index}
                            className="p-4 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-left"
                        >
                            {/* En-tête de la question + badge (Bonne / Mauvaise réponse) */}
                            <div className="flex items-start sm:items-center sm:justify-between flex-col sm:flex-row mb-3 gap-2">
                                <p className="text-lg font-medium text-gray-800 dark:text-gray-100">
                                    {question.text}
                                </p>
                                {userAnswer && (
                                    <span
                                        className={
                                            `px-2 py-1 text-sm font-semibold rounded-md border-2 ${
                                                questionIsCorrect
                                                    ? 'text-green-700 dark:text-green-200 bg-green-100 dark:bg-green-900 border-green-600'
                                                    : 'text-red-700 dark:text-red-200 bg-red-100 dark:bg-red-900 border-red-600'
                                            }`
                                        }
                                    >
                                        {questionIsCorrect ? 'Bonne réponse' : 'Mauvaise réponse'}
                                    </span>
                                )}
                            </div>

                            <div className="space-y-2">
                                {question.answers.map((answer, answerIdx) => {
                                    const isUserSelected = userSelectedAnswers.includes(answer.id);
                                    const isCorrect = answer.isCorrect;

                                    // Conteneur principal de la réponse
                                    let answerWrapperClasses =
                                        "flex items-start gap-2 p-3 rounded-md border-2 transition-all duration-300";

                                    // Carré de la lettre (A, B, C…)
                                    let letterBlockClasses =
                                        "flex items-center justify-center w-8 h-8 border-2 rounded-tl rounded-tr rounded-bl shrink-0 text-sm font-semibold";

                                    // Logique pour colorer la réponse
                                    if (isCorrect) {
                                        // Bonne réponse
                                        answerWrapperClasses +=
                                            " border-green-600 bg-green-50 dark:bg-green-900 text-green-700 dark:text-green-200";
                                        letterBlockClasses +=
                                            " border-green-600 bg-green-100 dark:bg-green-800 text-green-700 dark:text-green-200";
                                    } else if (isUserSelected) {
                                        // Mauvaise réponse sélectionnée
                                        answerWrapperClasses +=
                                            " border-red-600 bg-red-50 dark:bg-red-900 text-red-700 dark:text-red-200";
                                        letterBlockClasses +=
                                            " border-red-600 bg-red-100 dark:bg-red-800 text-red-700 dark:text-red-200";
                                    } else {
                                        // Ni correct ni sélectionné => neutre
                                        answerWrapperClasses +=
                                            " border-gray-300 dark:border-slate-600 bg-gray-100 dark:bg-slate-700 text-gray-800 dark:text-gray-200";
                                        letterBlockClasses +=
                                            " border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-200";
                                    }

                                    return (
                                        <div key={answer.id} className={answerWrapperClasses}>
                                            {/* Bloc lettre (A, B, C…) */}
                                            <div className={letterBlockClasses}>
                                                {letters[answerIdx]}
                                            </div>

                                            {/* Texte de la réponse */}
                                            <div className="break-words flex-1">
                                                {answer.text}
                                            </div>
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
                    <p className="text-lg text-gray-800 dark:text-gray-200 mb-4">
                        Voulez-vous soumettre votre score ?
                    </p>
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
                    <p className="text-lg text-gray-800 dark:text-gray-200 mb-4">
                        Créez un compte pour sauvegarder votre score et suivre votre progression !
                    </p>
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
