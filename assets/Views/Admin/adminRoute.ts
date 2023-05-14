import { RouteObject, Outlet } from "react-router";
import Layout from "./Layout";
import Overview from "./Overview/Overview";
import {default as UsersOverview} from "./Users/Overview/Overview";
import {default as ErrorsOverview} from "./Errors/Overview/Overview";
import Create from "./Users/Create/Create";
import List from "./Users/List/List";

const adminRoute:RouteObject = {
    path:"/admin",
    element:Layout(),
    children:[
        {
            path:"/admin/overview",
            element:Overview()
        },
        {
            path:"/admin/users",
            children:[
                {
                    path:"/admin/users/overview",
                    element: UsersOverview()
                },
                {
                    path:"/admin/users/list",
                    element: List()
                },
                {
                    path:"/admin/users/create",
                    element: Create()
                }
            ]
        },
        {
            path:"/admin/errors",
            children:[
                {
                    path:"/admin/errors/overview",
                    element: ErrorsOverview()
                }
            ]
        }

    ]
}

export default adminRoute;