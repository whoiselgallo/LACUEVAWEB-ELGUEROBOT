"use client";
import { useState } from "react";
import { AvatarClient } from "../../services/avatarClient";

export default function RegisterPage() {
  const [form, setForm] = useState({ name: "", email: "", password: "" });

  const handleRegister = async () => {
    await AvatarClient.register(form);
  };

  return (
    <main className="page">
      <h1 className="title-neon">Registro</h1>
      <input placeholder="Nombre" onChange={e => setForm({ ...form, name: e.target.value })} />
      <input placeholder="Correo" onChange={e => setForm({ ...form, email: e.target.value })} />
      <input type="password" placeholder="Contraseña" onChange={e => setForm({ ...form, password: e.target.value })} />
      <button className="btn-neon-magenta" onClick={handleRegister}>Crear cuenta</button>
    </main>
  );
}
