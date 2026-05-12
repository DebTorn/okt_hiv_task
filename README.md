### Projekt struktúra
# Felvételi Pontszámító Projekt Struktúra

```text
project-root/
│
├── index.php
├── config.php
├── errors.php
├── input.php
│
├── app/
│   ├── Calculators/
│   │   ├── AdmissionPointCalculator.php
│   │   ├── BasePointCalculator.php
│   │   ├── ExamBonusCalculator.php
│   │   ├── ExtraPointCalculator.php
│   │   │
│   │   └── Rules/
│   │    |  ├── AdvancedExamCalculationRule.php
│   │    |  └── LanguageExamCalculationRule.php
|   |    └── Interfaces/
|   |        └── BasePointCalculationRuleInterface.php
|   |        └── ExamBonusCalculationRuleInterface.php
|   |        └── ExtraPointCalculationRuleInterface.php
│   │
│   ├── Factories/
│   │   └── ApplicantFactory.php
│   │
│   ├── Models/
│   │   ├── Applicant.php
|   |   ├── Course.php
│   │   ├── ExamResult.php
│   │   └── ExtraPoint.php
│   │
│   ├── Providers/
│   │   └── Config.php
│   │
│   ├── Validators/
|   |   └── Interfaces/
|   |   |    └── ValidatorInterface.php
│   │   ├── MinimumPercentageValidator.php
│   │   ├── RequiredSubjectValidator.php
│   │   └── RequiredAndOptionalSubjectByMajorValidator.php
│   │
│
├── Tests/
|    ├── phpunit.xml
|    ├── Calculators/
|    ├── Factories/
|    ├── Others/
|    ├── Validators/
│
├── .gitignore
├── README.md
└── autoload.php
└── config.dist.php
└── errors.php
└── index.php
└── input.php
```

---

# Főbb komponensek

## index.php

CLI entrypoint.

```bash
php index.php
```

---

## config/

Globális konfigurációk.

### config.php

Pontszámítási szabályok:

* minimum százalék
* többletpont limitek
* szak követelmények

### errors.php

Hibakód → olvasható hibaüzenet.

---

## Calculators/

A pontszámítási logikákat tartalmazza

### AdmissionPointCalculator

Fő koordinátor.

### BasePointCalculator

Alappont számítás.

### ExamBonusCalculator

Emelt szintű érettségi pontok.

### ExtraPointCalculator

Nyelvvizsga és egyéb extra pontok.

---

## Rules/

Szabály alapú extra pont számítások.

Példa:

* emelt érettségi
* nyelvvizsga

---

## Validators/

Validációs réteg.

* kötelező tárgyak
* minimum százalék
* szak specifikus követelmények

---

## Factories/

Input → domain objektum konverzió és Applicant modellé alakítás

* ApplicantFactory

---

## Models/

Modellek.

* Applicant
* Course
* ExamResult
* ExtraPoint

---

# Lehetséges jövőbeli bővítések

* REST API
* Docker
* JSON input/output
* Localization (HU/EN)
* Adatbázis
* Admin felület
* Composer
