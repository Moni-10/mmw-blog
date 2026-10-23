import React, { useEffect, useState } from "react";
import { NavLink, useLocation, useNavigate } from "react-router-dom";
import api from "../api";

const links = [
  ["/", "Overview", "OV"], ["/websites", "Websites", "WS"], ["/seo", "SEO", "SE"],
  ["/add-blog", "Create blog", "CB"], ["/edit-blog", "Blog library", "BL"], ["/blog-categories", "Blog Categories", "BC"],
  ["/add-product", "Add product", "AP"], ["/products", "Products", "PR"], ["/product-categories", "Product Categories", "PC"], ["/profile", "Profile", "PF"],
];

export default function Navbar() {
  const location = useLocation(); const navigate = useNavigate();
  const [settings, setSettings] = useState({ companyName: "Blog Panel", username: "Administrator", logoUrl: "" });
  useEffect(() => { const load = () => api.get("/api/settings").then(({data}) => setSettings(old => ({...old, ...data}))).catch(() => {}); load(); window.addEventListener("settings-updated", load); return () => window.removeEventListener("settings-updated", load); }, []);
  if (location.pathname === "/login") return null;
  const displayName = localStorage.getItem("adminUsername") || settings.username;
  const logout = () => { if (window.confirm(`Log out from ${settings.companyName}?`)) { localStorage.removeItem("isLoggedIn"); localStorage.removeItem("adminApiKey"); localStorage.removeItem("adminUsername"); navigate("/login"); } };
  return <aside className="sidebar">
    <div className="brand mmw-sidebar-brand">{settings.logoUrl ? <img src={settings.logoUrl} alt={settings.companyName} /> : <img src="/mmw-logo.png" alt="Blog Panel" />}<div><strong>{settings.companyName}</strong><small>Content Manager</small></div></div>
    <nav className="side-nav"><span className="nav-label">Workspace</span>{links.map(([to,label,icon]) => <NavLink key={to} to={to} end={to === "/"} className={({isActive}) => `side-link ${isActive ? "active" : ""}`}><span className="nav-icon">{icon}</span>{label}</NavLink>)}</nav>
    <div className="sidebar-foot"><div className="admin-avatar">{displayName.slice(0, 2).toUpperCase()}</div><div className="admin-meta"><strong>{displayName}</strong><small>SEO workspace</small></div><button onClick={logout} className="logout-btn" aria-label="Log out" title="Log out">&rarr;</button></div>
    <button onClick={logout} className="sidebar-logout"><span>&rarr;</span> Log out</button>
  </aside>;
}
