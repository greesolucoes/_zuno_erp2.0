import React, { useState } from 'react';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

interface Props {
    logs: string[];
}

const logStyles = {
    logContainer: {
        maxHeight: '70vh',
        overflowY: 'auto' as const,
    },
    searchInput: {
        maxWidth: '300px',
    },
    preFormat: {
        fontFamily: 'monospace',
        fontSize: '0.875rem',
        whiteSpace: 'pre-line' as const,
        backgroundColor: 'transparent',
        wordBreak: 'break-word' as const,
        margin: 0,
    },
    emptyState: {
        fontSize: '4rem',
    },
};

export default function LogsIndex({ logs }: Props) {
    const [searchTerm, setSearchTerm] = useState('');

    const getLogLevelClass = (log: string) => {
        if (log.includes('[ERROR]')) return 'text-danger';
        if (log.includes('[WARNING]')) return 'text-warning';
        return 'text-success';
    };

    const getLogLevelBadge = (log: string) => {
        if (log.includes('[ERROR]')) {
            return <span className="badge bg-danger">Erro</span>;
        }
        if (log.includes('[WARNING]')) {
            return <span className="badge bg-warning">Aviso</span>;
        }
        return <span className="badge bg-success">Info</span>;
    };

    const filteredLogs = logs.filter((log) =>
        log.toLowerCase().includes(searchTerm.toLowerCase()),
    );

    return (
        <div className="container py-4">
            <div className="card shadow">
                <div className="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 className="mb-0">
                        <i className="bi bi-journal-text me-2"></i>
                        Logs do Sistema
                    </h5>
                    <div
                        className="input-group w-auto"
                        style={logStyles.searchInput}
                    >
                        <span className="input-group-text bg-primary border-0 text-white">
                            <i className="bi bi-search"></i>
                        </span>
                        <input
                            type="text"
                            className="form-control form-control-sm"
                            placeholder="Buscar logs..."
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                        />
                    </div>
                </div>

                <div className="card-body p-0">
                    <div style={logStyles.logContainer}>
                        {filteredLogs.length > 0 ? (
                            <div className="table-responsive">
                                <table className="table table-hover table-striped mb-0">
                                    <thead className="table-light">
                                        <tr>
                                            <th style={{ width: '100px' }}>
                                                Nível
                                            </th>
                                            <th>Mensagem</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {filteredLogs.map((log, index) => (
                                            <tr key={index}>
                                                <td>{getLogLevelBadge(log)}</td>
                                                <td>
                                                    <pre
                                                        className={`mb-0 ${getLogLevelClass(log)}`}
                                                        style={
                                                            logStyles.preFormat
                                                        }
                                                    >
                                                        {log}
                                                    </pre>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        ) : (
                            <div className="text-center py-5">
                                <i
                                    className="bi bi-inbox"
                                    style={logStyles.emptyState}
                                ></i>
                                <p className="text-muted mt-3">
                                    Nenhum log encontrado para esta data.
                                </p>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}
