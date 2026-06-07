import clsx from 'clsx';
import { useState } from 'react';

export function Tabs({ tabs, defaultTab, className }) {
    const [activeTab, setActiveTab] = useState(defaultTab ?? tabs[0]?.id);

    const active = tabs.find((tab) => tab.id === activeTab) ?? tabs[0];

    return (
        <div className={className}>
            <div role="tablist" aria-label="Onglets" className="flex gap-1 rounded-lg border border-slate-200 bg-slate-50 p-1">
                {tabs.map((tab) => (
                    <button
                        key={tab.id}
                        type="button"
                        role="tab"
                        id={`tab-${tab.id}`}
                        aria-selected={activeTab === tab.id}
                        aria-controls={`panel-${tab.id}`}
                        className={clsx(
                            'ds-focus flex-1 rounded-md px-3 py-2 text-sm font-medium transition-colors',
                            activeTab === tab.id
                                ? 'bg-white text-slate-900 shadow-sm'
                                : 'text-slate-600 hover:text-slate-900',
                        )}
                        onClick={() => setActiveTab(tab.id)}
                    >
                        {tab.label}
                    </button>
                ))}
            </div>
            <div
                role="tabpanel"
                id={`panel-${active?.id}`}
                aria-labelledby={`tab-${active?.id}`}
                className="mt-4 text-sm text-slate-600"
            >
                {active?.content}
            </div>
        </div>
    );
}
