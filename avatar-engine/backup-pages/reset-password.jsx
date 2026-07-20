import { useState } from "react";
import { useRouter } from "next/router";
import Link from "next/link";

export default function ResetPasswordPage() {
  const router = useRouter();
  const { token } = router.query;

  const [form, setForm] = useState({
    password: "",
    confirm: ""
  });

  const handleChange = (e) => {
    setForm({
      ...form,
      [e.target.name]: e.target.value
    });
  };

  const handleReset = (e) => {
    e.preventDefault();
    console.log("Restablecer contraseña con token:", token, form);
  };

  return (
    <main className="login-page">

      <div className="login-card-neon">

        <h1 className="title-neon login-title">
          Restablecer contraseña
        </h1>

        <p className="subtitle-neon login-subtitle">
          Ingresa tu nueva contraseña para continuar
        </p>

        <form className="login-form" onSubmit={handleReset}>

          <input
            type="password"
            name="password"
            placeholder="Nueva contraseña"
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
            Guardar nueva contraseña
          </button>
        </form>

        <p className="login-footer-text">
          ¿Ya recordaste tu contraseña?
          <Link href="/login" className="link-neon"> Inicia sesión</Link>
        </p>

      </div>

    </main>
  );
}
