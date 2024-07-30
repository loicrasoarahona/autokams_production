select
    sum(quantite),
    date(daty) as date
from
    vente_detail
    join vente on vente.id = vente_id
    join produit on produit.id = produit_id
where
    produit_id = 4
    and vente.daty >= :dateDebut
    and vente.daty <= :dateFin
group by
    date;

select
    sum(quantite),
    date(daty) as date
from
    vente_detail
    join vente on vente.id = vente_id
    join produit on produit.id = produit_id
where
    produit_id = 4
    and vente.daty >= :dateDebut
    and vente.daty <= :dateFin
group by
    date;

select
    sum(quantite),
    date(daty) as date
from
    vente_detail
    join vente on vente.id = vente_id
    join produit on produit.id = produit_id
where
    produit_id = 4
    and vente.daty >= :dateDebut
    and vente.daty <= :dateFin
group by
    date;