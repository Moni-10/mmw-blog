import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import api from "./api";

const LoginPage = () => {
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const [showPassword, setShowPassword] = useState(false);
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();
  const handleLogin = async (event) => {
    event.preventDefault(); setLoading(true); setError("");
    try {
      const response = await api.post("/api/admin/login", {
        username: username.trim(),
        password,
      });
      const apiKey = response.data?.apiKey;
      if (!response.data?.success || !apiKey) throw new Error("The server returned an invalid login response.");

      localStorage.setItem("adminApiKey", apiKey);
      localStorage.setItem("isLoggedIn", "true");
      localStorage.setItem("adminUsername", username.trim());
      navigate("/");
    }
    catch (err) {
      localStorage.removeItem("adminApiKey");
      localStorage.removeItem("isLoggedIn");
      localStorage.removeItem("adminUsername");
      if (!err.response) {
        setError(err.code === "ECONNABORTED"
          ? "Server Connection Error: the login server did not respond in time."
          : "Server Connection Error: the login server cannot be reached.");
      } else {
        setError(err.response.data?.message || err.response.data?.error || "Invalid Username or Password");
      }
    }
    finally { setLoading(false); }
  };
  return (
    <main className="mmw-login-page">
      <header className="mmw-login-hero">
        <img className="mmw-brand-logo" src="/mmw-logo.png" alt="Blog Panel" />
      </header>
      <section className="mmw-login-stage">
        <img className="mmw-login-character mmw-login-character-left" src="/blog-panel-character.png" alt="Blog creator working on a laptop" />
        <form onSubmit={handleLogin} className="login-card">
          <h2>Blog Panel</h2><p>Please log in to manage your blogs</p>
          {error && <div className="notice error" role="alert">{error}</div>}
          <label htmlFor="admin-username">Email/Username*</label>
          <div className="mmw-input-wrap"><input id="admin-username" value={username} onChange={(e) => setUsername(e.target.value)} autoComplete="username" required placeholder="Enter username" /></div>
          <label htmlFor="admin-password">Password*</label>
          <div className="mmw-input-wrap"><input id="admin-password" type={showPassword ? "text" : "password"} value={password} onChange={(e) => setPassword(e.target.value)} autoComplete="current-password" required placeholder="Enter password" /><button className="password-eye" type="button" onClick={() => setShowPassword((value) => !value)} aria-label={showPassword ? "Hide password" : "Show password"}><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-6 10-6 10 6 10 6-3.6 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>{showPassword && <path className="eye-slash" d="M4 4l16 16" />}</svg></button></div>
          <div className="mmw-login-options"><label><input type="checkbox" /> Remember me</label><button type="button">Forgot password?</button></div>
          <button className="login-submit" disabled={loading}>{loading ? "Signing in..." : "Login"}</button>
        </form>
        <img className="mmw-login-character mmw-login-character-right" src="/admin-character.png" alt="Blog Panel assistant" />
      </section>
    </main>
  );
};
export default LoginPage;
