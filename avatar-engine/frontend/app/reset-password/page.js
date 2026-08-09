"use client";
import { useState } from "react";
import { AvatarClient } from "../../services/avatarClient";

export default function ResetPasswordPage() {
  const [password, setPassword] = useState("");

  const handleChange = async () => {
    await AvatarClient.resetPassword(password);
  };

  return (
    <main className="page">
      <h1 className="title-neon">Restablecer contraseña</h1>
      <input type="password" placeholder="Nueva contraseña" value={password} onChange={e => setPassword(e.target.value)} />
      <button className="btn-neon-cyan" onClick={handleChange}>Actualizar</button>
    </main>
  );
}
