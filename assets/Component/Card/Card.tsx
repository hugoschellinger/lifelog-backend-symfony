import React, { ReactElement } from "react";
import style from "./Card.module.scss";

interface IProps extends React.DetailedHTMLProps<React.HTMLAttributes<HTMLDivElement>, HTMLDivElement>
{
    children : ReactElement;
    title?:string;
}

export default function Card({title,children,className, ...other}:IProps){

    return (
        <div>
            {title ? <p className={style.title}>{title.toLocaleUpperCase()}</p> : null}
            <div className={style.card + " "+className} {...other}>
                {children}
            </div>
        </div>
    )
}