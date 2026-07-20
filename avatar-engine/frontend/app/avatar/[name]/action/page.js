"use client";

import { useState } from "react";
import { useParams, useRouter } from "next/navigation";
import { AvatarClient } from "../../../../services/avatarClient";
import ActionSelector from "../../../../components/ActionSelector";
import AvatarPreview from "../../../../components/AvatarPreview";
import Loader from "../../../../components/Loader";

export default function AvatarActionPage() {
  const { name } = useParams();
  const router = useRouter();

  const [avatarURL, setAvatarURL] = useState(null);
  const [loading, setLoading] = useState(false);

  const handleAction = async (action) => {
    setLoading(true);

    const res = await AvatarClient.generateAction(name, action);

    setAvatarURL(res?.version?.url || null);
    setLoading(false);

    // Redirigir al perfil del avatar después de generar
    setTimeout(() => {
      router.push(`/avatar/${name}`);
    }, 1500);
  };

  return (
    <main className="page">
      <h2 className="title-neon">Nueva actividad para: {name}</h2>

      <ActionSelector onSubmit={handleAction} />

      {loading && <Loader text="Generando actividad..." />}

      {avatarURL && (
        <AvatarPreview
          src={avatarURL}
          label="Avatar en actividad"
        />
      )}
    </main>
  );
}
