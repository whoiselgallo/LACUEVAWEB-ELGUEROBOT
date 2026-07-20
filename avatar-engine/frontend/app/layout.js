import "./styles/global.css";
import "./styles/neon.css";

import Navbar from "../components/Navbar";
import Footer from "../components/Footer";

export const metadata = {
  title: "Avatar Engine — La Cueva del Güero",
  description: "Crea avatares con estilo urbano neón.",
};

export default function RootLayout({ children }) {
  return (
    <html lang="es">
      <body>
        <Navbar />
        <main>{children}</main>
        <Footer />
      </body>
    </html>
  );
}
