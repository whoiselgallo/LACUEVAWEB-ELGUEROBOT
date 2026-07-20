import { useRouter } from "next/router";
import { useEffect, useState } from "react";
import { AvatarClient } from "../../services/avatarClient";
import AvatarPreview from "../../components/AvatarPreview";
import Link from "next/link";

export default function AvatarPage() {
  const router = useRouter();
  const { name } = router.query;

  const [profile, setProfile] = useState(null);

  useEffect(() => {
    if (!name) return;
    AvatarClient.getAvatarProfile(name).then(setProfile);
  }, [name]);

  if (!profile) return <p>Cargando...</p>;

  return (
    <main className="page">
      <h2>Avatar: {profile.name}</h2>

      <AvatarPreview src={profile.avatar_base_url} label="Avatar base" />

      <h3>Versiones</h3>
      <ul>
        {profile.versions?.map((v) => (
          <li key={v.id}>
            {v.action} — <img src={v.url} width={80} />
          </li>
        ))}
      </ul>

      <Link href={`/avatar/${name}/action`}>Generar nueva actividad</Link>
    </main>
  );
}
