import Layout from "../components/Layout";
import AvatarPreview from "../components/AvatarPreview";
import ActionSelector from "../components/ActionSelector";
import Loader from "../components/Loader";

export default function AvatarEngineDashboard() {
  return (
    <Layout>
      <section className="dashboard-section">
        <h1 className="neon-title">Panel de Control — Avatar Engine</h1>
        <p className="neon-subtitle">
          Gestiona tus avatares, acciones y efectos visuales en tiempo real.
        </p>

        <div className="dashboard-grid">
          <AvatarPreview />
          <ActionSelector />
        </div>

        <Loader />
      </section>
    </Layout>
  );
}
