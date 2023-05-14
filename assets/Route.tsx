import React from "react";
import ReactDOM from "react-dom/client";
import {
  createBrowserRouter,
  Link,
  NavLink,
  Outlet,
  RouterProvider,
} from "react-router-dom";
import adminRoute from "./Views/Admin/adminRoute";

const router = createBrowserRouter([
  {
    path: "",
    element: (
      <>
        <div>Hello world!</div>
        <NavLink to="/admin"><p>Dashboard Admin</p></NavLink>
        <Outlet />
      </>
    ),
  },
  adminRoute
]);

ReactDOM.createRoot(document.getElementById("root")).render(
  <React.StrictMode>
    <RouterProvider router={router} />
  </React.StrictMode>
);
