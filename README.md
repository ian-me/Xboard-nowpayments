# XBoard NOWPayments

XBoard에서 NOWPayments의 호스티드 인보이스를 이용해 암호화폐 결제를 받을 수 있는 결제 플러그인입니다. 결제 알림(IPN)은 HMAC-SHA512 서명을 확인한 뒤 처리합니다.

- 플러그인 버전: `1.0.0`
- XBoard 요구 버전: `>=1.0.0`
- 플러그인 파일: [`Nowpayments/`](Nowpayments/)
- 상세 설치·운영 안내: [`Nowpayments/README.md`](Nowpayments/README.md)
- 릴리스 ZIP: [최신 버전 다운로드](https://github.com/ian-me/Xboard-nowpayments/releases/latest/download/Nowpayments-v1.0.0.zip)

## 기능

- NOWPayments 호스티드 결제 인보이스 생성 및 결제 페이지로 이동
- `x-nowpayments-sig` 헤더의 HMAC-SHA512 서명 검증
- 기본적으로 `finished` 상태만 결제 완료 처리
- 선택 설정으로 `confirmed` 상태도 완료 처리 가능
- 샌드박스 API, 고정 결제 코인, 환율 및 수수료 부담 설정 지원

## 설치

1. 저장소를 내려받거나 릴리스 ZIP을 풀고, `Nowpayments` 폴더 전체를 XBoard의 `plugins/` 폴더에 복사합니다. 설치 후 경로는 다음과 같아야 합니다.

   ```text
   plugins/Nowpayments/Plugin.php
   plugins/Nowpayments/config.json
   ```

2. XBoard 관리자 화면의 **플러그인** 메뉴에서 **NOWPayments**를 설치하고 활성화합니다.
3. **결제 설정 → 추가 → 결제 방식**에서 **NOWPayments**를 선택하고 설정을 입력합니다.
4. Docker 환경에서는 변경 사항 반영을 위해 컨테이너 재시작 또는 `php artisan octane:reload`가 필요할 수 있습니다.

## 설정

| 항목 | 설명 |
|---|---|
| API Key | NOWPayments 대시보드의 **Store Settings → API keys**에서 발급합니다. 필수 항목입니다. |
| IPN Secret | **Store Settings → Instant payment notifications**에서 발급합니다. 필수 항목입니다. |
| Price Currency | 인보이스 금액 기준 통화 코드입니다. 기본값은 `usd`입니다. |
| Exchange Rate | 사이트 통화 기준으로 1단위의 Price Currency가 얼마인지 입력합니다. 기본값은 `1`입니다. 예: 사이트 통화가 CNY이고 기준 통화가 USD이면 `7.2`. |
| Fixed Pay Currency | 결제 코인을 고정하려면 `usdttrc20`처럼 입력합니다. 비워 두면 결제자가 선택합니다. |
| Fee Paid By User | `1`이면 결제자가 수수료를 부담합니다. 기본값은 `0`입니다. |
| Accept "confirmed" | `1`이면 `confirmed`도 결제 완료로 처리합니다. 기본적으로는 `finished`만 허용합니다. |
| Sandbox Mode | `1`이면 샌드박스 API를 사용합니다. 샌드박스용 키가 필요하며 기본값은 `0`입니다. |

결제 금액은 XBoard의 `total_amount`(센트 단위)를 `100 × Exchange Rate`로 나누어 산출합니다. IPN 알림 URL은 XBoard가 인보이스 생성 요청에 전달하므로 NOWPayments 대시보드에 별도로 등록하지 않아도 됩니다. 단, 알림 URL이 인터넷에서 패널로 연결 가능해야 합니다.

## 결제 처리 및 주의 사항

결제자는 NOWPayments 호스티드 인보이스 페이지에서 결제합니다. XBoard는 콜백 서명을 확인하고, 허용된 결제 상태와 주문 번호 및 결제 ID가 유효할 때 주문을 완료 처리합니다. `waiting`, `confirming`, `partially_paid`, `failed`, `expired` 등은 자동 완료되지 않습니다. 부분 결제는 NOWPayments 대시보드에서 직접 확인하세요.

운영 환경에서는 HTTPS를 사용하고 API Key와 IPN Secret을 공개하지 마세요. 실제 결제 전에 샌드박스 모드로 연동을 확인하는 것을 권장합니다.

## 라이선스 및 면책

MIT 라이선스로 배포됩니다. XBoard 또는 NOWPayments가 공식적으로 제공하거나 보증하는 플러그인이 아닙니다. 자세한 조건은 [`LICENSE`](LICENSE)를 확인하세요.
