import React from "react";
import ReactDOM from "react-dom/client";
import { createBrowserRouter, Link, NavLink, Outlet, RouterProvider } from "react-router-dom";

const router = createBrowserRouter([
  {
    path: "",
    element: <><div>Hello world!</div><Link to="/zebi">
    <p>Visit your profile</p>
  </Link><Outlet /></>,
    children: [
      {
        path: "/zebi",
        element: <><div>Hello!</div>      <Link to="/">
        <p>Visit your profile</p>
      </Link></>,
      },
    ],
  },
]);

ReactDOM.createRoot(document.getElementById("root")).render(
  <React.StrictMode>
    <RouterProvider router={router} />
  </React.StrictMode>
);
