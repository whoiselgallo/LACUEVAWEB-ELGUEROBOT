"use client";
import { useState } from "react";
import { AvatarClient } from "../../services/avatarClient";

export default function ForgotPasswordPage() {
  const [email, setEmail] = useState("");

  const handleReset = async () => {
    await AvatarClient.sendResetLink(email);
  };

  return (
    <main className="page">
      <h1 className="title-neon">Recuperar contraseña</h1>
      <input type="email" placeholder="Correo electrónico" value={email} onChange={e => setEmail(e.target.value)} />
      <button className="btn-neon-magenta" onClick={handleReset}>Enviar enlace</button>
    </main>
  );
}
