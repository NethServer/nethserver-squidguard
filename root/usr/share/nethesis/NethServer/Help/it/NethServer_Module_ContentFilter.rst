====================
Filtro contenuti web
====================

Il filtro contenuti serve per controllare la navigazione web ed
impostare dei blocchi in base ad alcuni elementi quali parole chiave, IP
interni, utenti interni, valutazione del contenuto della pagina web,
estensioni dei file. Grazie a questo strumento è possibile ad esempio abilitare
l'accesso solo su alcuni siti desiderati (ad esempio quelli di interesse
aziendale) bloccando tutti gli altri.

Il filtro contenuti è basato sui profili.
Un profilo è composto da tre parti:

* Chi: un host o un utente che accede al web
* Cosa: un filtro composto da categorie consentite o bloccate
* Quando: un lasso temporale nel quale l'accesso è filtrato

E' presente anche uno speciale profilo che si applica a tutti i client
in qualsiasi momento.

Generale
========

Configurazione generale comune per tutte le schede.

Abilita filtro
    Abilita o disabilita il filtro.

Abilita filtro con espressioni su URL
    Filtra gli URL utilizzando espressioni regolari.
    Per esempio, blocca gli URL che contengono la parola *sesso*.
    Non raccomandato: questo tipo di filtro può contenere falsi positivi.

Lista di estensioni file bloccate
    Lista separata da virgole di estensioni da bloccare.

Blacklist globale
   Lista di siti o URL sempre bloccati, può essere abilitata o disabilitata per ciascun filtro.

Whitelist globale
   Lista di siti o URL sempre permessi, può essere abilitata o disabilitata per ciascun filtro.


Profili
=======

Un profilo descrive chi può accedere contenuti in un lasso di tempo definito.

Nome
   Nome descrittivo univoco.

Chi
   Può essere:
   * un utente locale
   * un gruppo di utenti locali
   * un host
   * un gruppo di host
   * un utente di Active Directory, se il server ha effettuato il join al dominio

Cosa
   Un filtro precedentemente creato nella scheda Filtro o il filtro di default.

Quando
   Una condizione temporale creata nella scheda Condizioni temporali.

Descrizione
    Descrizione personalizzata (opzionale).


Filtri
======

un filtro descrive il tipo di contenuto permesso o bloccato.

Nome
   Nome descrittivo univoco.

Descrizione
    Descrizione personalizzata (opzionale).

Blocca accesso con IP ai siti web
    Se abilitato, i client non possono accedere ai siti usando l'indirizzo IP ma solo il nome host.

Abilita blacklist globale
    Abilita la blacklist definita nella scheda Generale.

Abilita whitelist globale
    Abilita la whitelist definita nella scheda Generale.

Lista di estensioni file bloccate
    Blocca tutte le estensioni di file definite nella scheda Generale.

Modalità
    Il filtro può lavorare in due modalità:

    * Blocca tutto, permetti contenuto selezionato: le categorie selezionate sono permesse, tutti gli altri siti bloccati
    * Permetti tutto, blocca contenuto selezionato: le categorie selezionate sono bloccate, tutti gli altri siti permessi

Categorie
    Lista di categorie derivate dalle blacklist configurate nella scheda Blacklist.
    Contiene inoltre le categorie personalizzate.

Condizioni temporali
====================

Definisce una lista di condizioni temporali.

Nome
   Nome descrittivo univoco.

Descrizione
    Descrizione personalizzata (opzionale).

Giorni della settimana
    Seleziona uno o più giorni della settimana.

Ora inizio
    Orario di inizio della condizione temporale.

Ora fine
    Orario di fine della condizione temporale.


Categorie personalizzate
========================

Le categorie personalizzate possono essere usate nella scheda Filtri.

Nome
   Nome descrittivo univoco.

Descrizione
    Descrizione personalizzata (opzionale).

Domini
    Lista di domini personalizzati, uno per linea.


Blacklist
=========

Le liste sono scaricare una volta al giorno durante la notte.
Le liste disponibili sono:

* Shalla (libera per uso non commerciale)
* UrlBlacklist.com (uso commerciale)
* Université Toulouse (libera))
* Personalizza: inserire un URL personalizzato, la lista deve essere 
  nel formato supportato da SquidGuard


.. raw:: html

   {{{INCLUDE NethServer_Module_ContentFilter_*.html}}}
