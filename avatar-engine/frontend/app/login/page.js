"use client";
import { useState } from "react";
import { AvatarClient } from "../../services/avatarClient";

export default function LoginPage() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");

  const handleLogin = async () => {
    await AvatarClient.login(email, password);
  };

  return (
    <main className="page">
      <h1 className="title-neon">Iniciar sesión</h1>
      <input type="email" placeholder="Correo" value={email} onChange={e => setEmail(e.target.value)} />
      <input type="password" placeholder="Contraseña" value={password} onChange={e => setPassword(e.target.value)} />
      <button className="btn-neon-cyan" onClick={handleLogin}>Entrar</button>
    </main>
  );
}
