# imgExifInfo

plugin pour Dotclear

Ce plugin, utilisé dans le cadre d'une boucle &lt;tpl:Entry&gt; permet d'ajouter des données à l'avant ou à l'arrière des éléments html &lt;img&gt; présents dans les posts ou les pages
et de modifier les attributs title et class de ces éléments html &lt;img&gt; ou d'ajouter un attribut title ou class si celui-ci n'est pas présent.

## Balises tpl

### {{tpl:ImgExifInfoExcerpt}} et {{tpl:ImgExifInfoContent}}

Ces deux balises s'utilisent de la même façon et ont les mêmes attributs. La balise {{tpl:ImgExifInfoExcerpt}} remplace
{{tpl:EntryExcerpt}} et la balise {{tpl:ImgExifInfoContent}} remplace {{tpl:EntryContent}} dans le cadre d'une boucle
&lt;tpl:Entry&gt;.

#### Attributs

##### before

Le texte à insérer avant la balise <img>

Valeur par défaut: ""

##### after

Le texte à insérer après la balise <img>

Valeur par défaut: ""

##### title

Le texte à insérer dans l'attribut title de la balise <img>

Valeur par défaut: ""

Ces 3 attributs fonctionnent de la même façon. Ils peuvent contenir du texte ou des références vers les données exif
présentes dans la photo affichée dans la balise &lt;img&gt;.

Références des données exif:

- %Make% sera remplacé par la marque de l'appareil photo

- %Model% sera remplacé par le modèle de l'appareil photo

- %FocalLength% sera remplacé par la focale utilisée

- %FNumber% sera remplacé par l'ouverture utilisée

- %ExposureTime% sera remplacé par le temps d'exposition

- %ISOSpeedRatings% sera remplacé par la sensibilité ISO

Pour l'attribut title, la référence %Title% peut également être utilisée pour insérer l'ancienne valeur de l'attribut title

Les textes seront insérés uniquement si:

- la photo est stockée sur le serveur dans le répertoire 'public' de Dotclear ou un sous-répertoire de celui-ci

- les données exif d'ouverture (FNumber), d'exposition (ExposureTime), de sensibilité ISO (ISOSpeedRatings)
et de longueur focale (FocalLength) ont été détectées

-les fichiers ont une extension .jpg, .jpeg, .tif ou .tiff (en minuscules ou majuscules) 

##### addClass

Cet attribut permet d'ajouter une classe css à la balise &lt;img&gt;

- si 'attribut vaut "0", aucune classe n'est ajoutée.

- si l'attribut vaut "1" la classe "Landscape" ou "Portrait" sera ajoutée, selon l'orientation de la photo.

- si l'attribut est une chaine de caractère, cette chaine sera utilisée comme préfixe de nom de classe.

- si la balise &lt;img&gt; a déjà un attribut class, la nouvelle classe sera ajoutée aux classes déjà présente dans l'attribut
et dans le cas contraire, un nouvel attribut class sera créé

Valeur par défaut: "0"

## quelques exemples

### Ajouter les données exif dans le title des images

```
{{tpl:ImgExifInfoContent title="%Title% - %Make% %Model% %FocalLength% mm, f %FNumber%, %ExposureTime% sec., %ISOSpeedRatings% ISO"}}
```

### Ajouter les données exif dans une balise html &lt;div&gt; sous l'image

```
{{tpl:ImgExifInfoContent after="<div>%Make% %Model% %FocalLength% mm, f %FNumber%, %ExposureTime% sec., %ISOSpeedRatings% ISO</div>"}}
```

Petit rappel de CSS pour que cet exemple fonctionne correctement: img est un élément inline, tandis que div est un élément block. Songez à modifier cela avec CSS 
pour que cet exemple soit affiché correctement ou mieux, voyez l'exemple suivant.

### Encapsuler la balise html &lt;img&gt; dans une balise html &lt;figure&gt; et ajouter à cette balise html &lt;figure&gt; une balise &lt;figcaption&gt; avec les données exif 

```
{{tpl:ImgExifInfoContent before="<figure>" after="<figcaption>%Make% %Model% %FocalLength% mm, f %FNumber%, %ExposureTime% sec., %ISOSpeedRatings% ISO</figcaption></figure>"}}
```

### Ajouter une classe à l'image avec l'orientation de celle-ci

```
{{tpl:ImgExifInfoContent addClass="maClasse"}}
```

et lors de la création du html par Dotclear les balises &lt;img&gt; deviendront:

```
<img class="maClasseLandscape" src="....." / >
<img class="maClassePortrait" src="....." / >
```
