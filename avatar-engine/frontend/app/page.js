import Link from "next/link";

export default function Home() {
  return (
    <main className="page">
      <div className="home-container">

        <h1 className="title-neon">
          Avatar Engine — La Cueva del Güero
        </h1>

        <p className="subtitle-neon">
          Genera avatares con estilo urbano neón, actividades dinámicas y estética única.
        </p>

        <hr className="divider-neon" />

        <div className="menu-buttons">
          <Link href="/create" className="btn-neon-green">
            Crear nuevo avatar
          </Link>

          <Link href="/avatars" className="btn-neon-cyan">
            Ver avatares existentes
          </Link>
        </div>

      </div>
    </main>
  );
}
