import React, { useRef, useState, useEffect } from 'react';
import { Trash2, UploadCloud } from 'lucide-react';

export default function DropZone({ name = 'file' }) {
    const inputRef = useRef();
    const [fileName, setFileName] = useState(null);
    const [file, setFile] = useState(null);
    const [previewUrl, setPreviewUrl] = useState(null);

    const handleFileChange = (e) => {
        const selectedFile = e.target.files?.[0];
        if (selectedFile) {
            setFileName(selectedFile.name);
            setFile(selectedFile);
            if (selectedFile.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onloadend = () => setPreviewUrl(reader.result);
                reader.readAsDataURL(selectedFile);
            } else {
                setPreviewUrl(null);
            }
        }
    };

    const resetFile = (e) => {
        e.preventDefault();
        e.stopPropagation();
        inputRef.current.value = '';
        setFileName(null);
        setFile(null);
        setPreviewUrl(null);
    };

    const onDrop = (e) => {
        e.preventDefault();
        const droppedFile = e.dataTransfer.files?.[0];
        if (droppedFile) {
            inputRef.current.files = e.dataTransfer.files;
            setFileName(droppedFile.name);
            setFile(droppedFile);
            if (droppedFile.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onloadend = () => setPreviewUrl(reader.result);
                reader.readAsDataURL(droppedFile);
            } else {
                setPreviewUrl(null);
            }
        }
    };

    const onDragOver = (e) => {
        e.preventDefault();
    };

    return (
        <div
            onDrop={onDrop}
            onDragOver={onDragOver}
            className="relative border-2 border-dashed border-gray-300 dark:border-gray-700 p-6 rounded-lg text-center hover:bg-gray-50 dark:hover:bg-primary-1000 transition duration-200"
        >
            <input
                ref={inputRef}
                type="file"
                name={name}
                onChange={handleFileChange}
                className="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-10"
            />

            {!file && (
                <div className="flex flex-col items-center justify-center gap-2 pointer-events-none">
                    <UploadCloud className="w-8 h-8 text-gray-400" />
                    <p className="text-gray-600 dark:text-gray-400 text-sm">Glissez un fichier ici ou cliquez pour choisir</p>
                </div>
            )}

            {file && (
                <div className="flex flex-col items-center gap-3 pointer-events-none">
                    <div className="flex flex-col sm:flex-row items-center justify-between w-full gap-4">
                        {previewUrl && (
                            <img src={previewUrl} alt="Preview" className="w-24 h-24 object-cover rounded shadow shadow-slate-400 dark:shadow-primary-500" />
                        )}
                        <div className="flex-1 flex items-center justify-between w-full">
                            <span className="text-sm truncate text-gray-800 dark:text-gray-200 text-left">{fileName}</span>
                            <button
                                type="button"
                                className="ml-4 text-danger z-20 pointer-events-auto"
                                onClick={resetFile}
                            >
                                <Trash2 className="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
