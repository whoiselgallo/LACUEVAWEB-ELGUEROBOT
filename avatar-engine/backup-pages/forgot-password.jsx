import { useState } from "react";
import Link from "next/link";

export default function ForgotPasswordPage() {
  const [email, setEmail] = useState("");

  const handleRecover = (e) => {
    e.preventDefault();
    console.log("Recuperar contraseña para:", email);
  };

  return (
    <main className="login-page">

      <div className="login-card-neon">

        <h1 className="title-neon login-title">
          Recuperar contraseña
        </h1>

        <p className="subtitle-neon login-subtitle">
          Ingresa tu correo y te enviaremos instrucciones
        </p>

        <form className="login-form" onSubmit={handleRecover}>
          <input
            type="email"
            placeholder="Correo electrónico"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            className="input-neon"
          />

          <button type="submit" className="btn-neon-green login-btn">
            Enviar enlace de recuperación
          </button>
        </form>

        <p className="login-footer-text">
          ¿Recordaste tu contraseña?
          <Link href="/login" className="link-neon"> Inicia sesión</Link>
        </p>

      </div>

    </main>
  );
}
