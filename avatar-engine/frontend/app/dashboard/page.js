import Link from "next/link";
import Layout from "../components/Layout";

export default function DashboardPage() {
  return (
    <Layout>
      <main className="dashboard-page">

        <h1 className="title-neon dashboard-title">
          Panel Administrativo — Avatar Engine
        </h1>

        <p className="subtitle-neon dashboard-subtitle">
          Controla avatares, actividades y configuraciones del sistema
        </p>

        <div className="dashboard-grid">

          <Link href="/create" className="dashboard-card">
            <div className="card-icon">🎨</div>
            <h3>Crear Avatar</h3>
            <p>Genera un nuevo avatar base desde una foto real.</p>
          </Link>

          <Link href="/avatars" className="dashboard-card">
            <div className="card-icon">🧑‍🚀</div>
            <h3>Ver Avatares</h3>
            <p>Explora todos los avatares generados y sus actividades.</p>
          </Link>

          <Link href="/settings" className="dashboard-card">
            <div className="card-icon">⚙️</div>
            <h3>Configuración</h3>
            <p>Ajustes del sistema, API Keys y preferencias.</p>
          </Link>

          <Link href="/logs" className="dashboard-card">
            <div className="card-icon">📄</div>
            <h3>Registros</h3>
            <p>Historial de generación, errores y auditoría.</p>
          </Link>

        </div>

      </main>
    </Layout>
  );
}
