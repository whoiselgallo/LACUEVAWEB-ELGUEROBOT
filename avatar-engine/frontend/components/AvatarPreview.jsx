export default function AvatarPreview({ src, label }) {
  if (!src) return null;

  return (
    <div className="avatar-preview">
      <img src={src} alt="avatar" />
      {label && <p>{label}</p>}
    </div>
  );
}
