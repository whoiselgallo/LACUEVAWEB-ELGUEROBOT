import { useRef } from "react";

export default function UploadBox({ onFile }) {
  const inputRef = useRef(null);

  const handleChange = (e) => {
    const file = e.target.files[0];
    if (file) onFile(file);
  };

  return (
    <div className="upload-box" onClick={() => inputRef.current.click()}>
      <p>Haz clic para subir una foto</p>
      <input
        ref={inputRef}
        type="file"
        accept="image/*"
        onChange={handleChange}
        style={{ display: "none" }}
      />
    </div>
  );
}
