import React from "react";
import style from "./Overview.module.scss";
import Card from "../../../Component/Card/Card";

const Overview=()=>{

    return (
        <>
            <p className={style.title}>OVERVIEW</p>
            <Card title="Salut">
                <p>Salut</p>
            </Card>
        </>
    )
}

export default Overview;