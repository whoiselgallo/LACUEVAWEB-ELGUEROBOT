import { useRouter } from "next/router";
import { useState } from "react";
import ActionSelector from "../../../components/ActionSelector";
import AvatarPreview from "../../../components/AvatarPreview";
import Loader from "../../../components/Loader";
import { AvatarClient } from "../../../services/avatarClient";

export default function AvatarActionPage() {
  const router = useRouter();
  const { name } = router.query;

  const [avatarURL, setAvatarURL] = useState(null);
  const [loading, setLoading] = useState(false);

  const handleAction = async (action) => {
    setLoading(true);

    const res = await AvatarClient.generateAction(name, action);

    setAvatarURL(res?.version?.url || null);
    setLoading(false);
  };

  return (
    <main className="page">
      <h2>Nueva actividad para: {name}</h2>

      <ActionSelector onSubmit={handleAction} />

      {loading && <Loader text="Generando actividad..." />}

      {avatarURL && <AvatarPreview src={avatarURL} label="Avatar en actividad" />}
    </main>
  );
}
