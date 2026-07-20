import Link from "next/link";

export default function Navbar() {
  return (
    <nav className="navbar-neon">
      <div className="navbar-inner">

        <Link href="/" className="navbar-logo">
          La Cueva del Güero
        </Link>

        <div className="navbar-links">
          <Link href="/create" className="nav-link-neon">Crear Avatar</Link>
          <Link href="/avatars" className="nav-link-neon">Avatares</Link>
          <Link href="/dashboard" className="btn-neon-green nav-cta">
            Dashboard
          </Link>
        </div>

      </div>
    </nav>
  );
}
