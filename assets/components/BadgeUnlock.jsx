import React from 'react'
import { X } from "lucide-react";

export function BadgeUnlock ({ name, description, theme, image }) {
    return (
        <modal-dialog className='badge-modal' overlay-close="true">
            <section className="modal-box">
                <button data-dismiss="true" aria-label="Close" className="modal-close">
                    <X size={24} />
                </button>
                <div className="stack">
                    <div className="flex justify-center items-center">
                        <div className={`mt-4 badge-icon badge-icon-${theme}`}>
                            <img alt='Badge' src={image}/>
                        </div>
                    </div>
                    <div className='h3 text-center mt-3 mb-2'>
                        Vous venez de débloquer le badge <span className='text-lead'>{name}</span> !
                    </div>
                    <hr className='my-4'/>
                    <div className='text-muted text-center text-lg mb-3'>"{description}"</div>
                </div>
            </section>
        </modal-dialog>
)
}