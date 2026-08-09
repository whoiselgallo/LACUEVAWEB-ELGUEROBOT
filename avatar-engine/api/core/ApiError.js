export class ApiError extends Error {
    constructor(message, status = 400) {
        super(message);
        this.status = status;
    }
}

export class NotFoundError extends ApiError {
    constructor(message = "Recurso no encontrado") {
        super(message, 404);
    }
}

export class ConflictError extends ApiError {
    constructor(message = "Conflicto de recursos") {
        super(message, 409);
    }
}

export class ServerError extends ApiError {
    constructor(message = "Error interno del servidor") {
        super(message, 500);
    }
}
