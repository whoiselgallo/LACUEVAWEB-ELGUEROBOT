import { useState } from "react";
import Link from "next/link";

export default function RegisterPage() {
  const [form, setForm] = useState({
    name: "",
    email: "",
    password: "",
    confirm: ""
  });

  const handleChange = (e) => {
    setForm({
      ...form,
      [e.target.name]: e.target.value
    });
  };

  const handleRegister = (e) => {
    e.preventDefault();
    console.log("Registro:", form);
  };

  return (
    <main className="login-page">

      <div className="login-card-neon">

        <h1 className="title-neon login-title">
          Crear cuenta
        </h1>

        <p className="subtitle-neon login-subtitle">
          Únete al panel de control del Avatar Engine
        </p>

        <form className="login-form" onSubmit={handleRegister}>

          <input
            type="text"
            name="name"
            placeholder="Nombre completo"
            value={form.name}
            onChange={handleChange}
            className="input-neon"
          />

          <input
            type="email"
            name="email"
            placeholder="Correo electrónico"
            value={form.email}
            onChange={handleChange}
            className="input-neon"
          />

          <input
            type="password"
            name="password"
            placeholder="Contraseña"
            value={form.password}
            onChange={handleChange}
            className="input-neon"
          />

          <input
            type="password"
            name="confirm"
            placeholder="Confirmar contraseña"
            value={form.confirm}
            onChange={handleChange}
            className="input-neon"
          />

          <button type="submit" className="btn-neon-green login-btn">
            Crear cuenta
          </button>
        </form>

        <p className="login-footer-text">
          ¿Ya tienes cuenta?
          <Link href="/login" className="link-neon"> Inicia sesión</Link>
        </p>

      </div>

    </main>
  );
}
