import { useState } from "react";

export default function ActionSelector({ onSubmit }) {
  const [action, setAction] = useState("");

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!action.trim()) return;
    onSubmit(action.trim());
  };

  return (
    <form onSubmit={handleSubmit} className="action-form">
      <input
        value={action}
        onChange={(e) => setAction(e.target.value)}
        placeholder="Ej. corriendo, sentado en la barra..."
      />
      <button type="submit">Generar actividad</button>
    </form>
  );
}
