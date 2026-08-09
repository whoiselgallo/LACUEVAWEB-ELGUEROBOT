import { useState } from "react";
import Link from "next/link";

export default function LoginPage() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");

  const handleLogin = (e) => {
    e.preventDefault();
    console.log("Login:", { email, password });
  };

  return (
    <main className="login-page">

      <div className="login-card-neon">

        <h1 className="title-neon login-title">
          Bienvenido a La Cueva del Güero
        </h1>

        <p className="subtitle-neon login-subtitle">
          Accede al panel de control del Avatar Engine
        </p>

        <form className="login-form" onSubmit={handleLogin}>
          <input
            type="email"
            placeholder="Correo electrónico"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            className="input-neon"
          />

          <input
            type="password"
            placeholder="Contraseña"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            className="input-neon"
          />

          <button type="submit" className="btn-neon-green login-btn">
            Iniciar sesión
          </button>
        </form>

        <p className="login-footer-text">
          ¿No tienes cuenta?  
          <Link href="/register" className="link-neon"> Regístrate</Link>
        </p>

      </div>

    </main>
  );
}
