"use client";

import { useEffect, useState } from "react";
import { useParams } from "next/navigation";
import Link from "next/link";
import { AvatarClient } from "../../../services/avatarClient";
import AvatarPreview from "../../../components/AvatarPreview";

export default function AvatarPage() {
  const { name } = useParams();
  const [profile, setProfile] = useState(null);

  useEffect(() => {
    if (!name) return;

    AvatarClient.getAvatarProfile(name).then((res) => {
      setProfile(res);
    });
  }, [name]);

  if (!profile) return <p>Cargando...</p>;

  return (
    <main className="page">
      <h2 className="title-neon">Avatar: {profile.name}</h2>

      <AvatarPreview
        src={profile.avatar_base_url}
        label="Avatar base"
      />

      <h3 className="subtitle-neon">Versiones</h3>

      <ul className="versions-list">
        {profile.versions?.map((v) => (
          <li key={v.id} className="version-item">
            {v.action} — <img src={v.url} width={80} />
          </li>
        ))}
      </ul>

      <Link
        href={`/avatar/${name}/action`}
        className="btn-neon-cyan"
      >
        Generar nueva actividad
      </Link>
    </main>
  );
}
