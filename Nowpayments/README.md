# NOWPayments for Xboard

NOWPayments 호스티드 인보이스 방식 결제 플러그인입니다.
유저가 결제하면 NOWPayments 결제 페이지로 이동하고, IPN 콜백(HMAC-SHA512 서명 검증)으로 주문이 자동 완료됩니다.

## 설치

1. 이 폴더(`Nowpayments`)를 Xboard의 `plugins/` 아래에 넣습니다.
   `plugins/Nowpayments/Plugin.php`, `plugins/Nowpayments/config.json`
2. 관리자 패널 > 플러그인에서 **NOWPayments** 설치 후 활성화
   (Docker라면 컨테이너 재시작 또는 `php artisan octane:reload` 필요할 수 있음)
3. 관리자 패널 > 결제 설정 > 추가 > 결제 방식에서 **NOWPayments** 선택

## NOWPayments 대시보드 설정

- Store Settings > API keys 에서 API Key 발급
- Store Settings > Instant payment notifications 에서 IPN Secret 발급
- 결제 설정 화면에 표시되는 알림 URL(`/api/v1/guest/payment/notify/NOWPayments/{uuid}`)은 인보이스 생성 시 자동으로 전달되므로 대시보드에 따로 넣지 않아도 됩니다.

## 설정 항목

| 항목 | 설명 |
|---|---|
| API Key / IPN Secret | 필수 |
| Price Currency | 인보이스 기준 통화 (기본 `usd`) |
| Exchange Rate | 사이트 통화 ÷ 기준 통화. 사이트가 CNY이고 기준 통화가 usd면 `7.2` 처럼 입력 (기본 1) |
| Fixed Pay Currency | 코인 고정 (예: `usdttrc20`), 비우면 유저가 선택 |
| Fee Paid By User | `1`이면 수수료를 유저가 부담 |
| Accept "confirmed" | `1`이면 `confirmed` 상태에서도 완료 처리 (기본은 `finished`만) |
| Sandbox Mode | `1`이면 샌드박스 API 사용 (샌드박스 키 필요) |

## 동작 메모

- 금액은 Xboard의 센트 단위 `total_amount`를 `/100/Exchange Rate`로 환산합니다.
- `finished`가 아닌 상태(waiting, confirming, partially_paid 등)는 주문을 완료하지 않고 실패 응답을 돌려주므로 NOWPayments가 몇 번 재전송합니다. 정상 동작입니다.
- 부분 결제(`partially_paid`)는 자동 완료되지 않습니다. NOWPayments 대시보드에서 직접 확인하세요.
- 알림 URL이 외부에서 접근 가능해야 합니다. 결제 설정의 알림 도메인(notify_domain)을 쓰는 경우 그 도메인이 패널로 연결돼야 합니다.
