<?php

declare(strict_types=1);

namespace Tests;

use KigaRoo\SnapshotTesting\MatchesSnapshots;
use KigaRoo\SnapshotTesting\Wildcard\DateTimeWildcard;
use KigaRoo\SnapshotTesting\Wildcard\StringWildcard;
use PHPUnit\Framework\TestCase;

class XmlTest extends TestCase
{
    use MatchesSnapshots;

    public function testXml() : void
    {
        $data = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.008.001.02" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:iso:std:iso:20022:tech:xsd:pain.008.001.02 pain.008.001.02.xsd">
  <CstmrDrctDbtInitn>
    <GrpHdr>
      <MsgId>string - to be replaced</MsgId>
      <CreDtTm>2011-11-11T11:11:11</CreDtTm>
      <InitgPty>
        <Nm>dummy biller 123</Nm>
      </InitgPty>
      <CtrlSum>13.32</CtrlSum>
      <NbOfTxs>2</NbOfTxs>
    </GrpHdr>
    <PmtInf>
      <PmtInfId>string - to be replaced</PmtInfId>
      <PmtMtd>DD</PmtMtd>
      <NbOfTxs>2</NbOfTxs>
      <CtrlSum>13.32</CtrlSum>
      <PmtTpInf>
        <SvcLvl>
          <Cd>SEPA</Cd>
        </SvcLvl>
        <LclInstrm>
          <Cd>CORE</Cd>
        </LclInstrm>
        <SeqTp>FRST</SeqTp>
      </PmtTpInf>
      <ReqdColltnDt>2019-09-03</ReqdColltnDt>
      <Cdtr>
        <Nm>dummy biller</Nm>
      </Cdtr>
      <CdtrAcct>
        <Id>
          <IBAN>TEST IBAN</IBAN>
        </Id>
        <Ccy>EUR</Ccy>
      </CdtrAcct>
      <CdtrAgt>
        <FinInstnId>
          <BIC>TEST BIC</BIC>
        </FinInstnId>
      </CdtrAgt>
      <ChrgBr>SLEV</ChrgBr>
      <DrctDbtTxInf>
        <PmtId>
          <EndToEndId>string - to be replaced/0</EndToEndId>
        </PmtId>
        <InstdAmt Ccy="EUR">6.66</InstdAmt>
        <DrctDbtTx>
          <MndtRltdInf>
            <MndtId>A1</MndtId>
            <DtOfSgntr>2019-08-28</DtOfSgntr>
          </MndtRltdInf>
          <CdtrSchmeId>
            <Id>
              <PrvtId>
                <Othr>
                  <Id>foo</Id>
                  <SchmeNm>
                    <Prtry>SEPA</Prtry>
                  </SchmeNm>
                </Othr>
              </PrvtId>
            </Id>
          </CdtrSchmeId>
        </DrctDbtTx>
        <DbtrAgt>
          <FinInstnId>
            <BIC>TEST BIC</BIC>
          </FinInstnId>
        </DbtrAgt>
        <Dbtr>
          <Nm>foo</Nm>
        </Dbtr>
        <DbtrAcct>
          <Id>
            <IBAN>TEST IBAN</IBAN>
          </Id>
        </DbtrAcct>
        <RmtInf>
          <Ustrd>1, foo</Ustrd>
        </RmtInf>
      </DrctDbtTxInf>
      <DrctDbtTxInf>
        <PmtId>
          <EndToEndId>string - to be replaced/1</EndToEndId>
        </PmtId>
        <InstdAmt Ccy="EUR">6.66</InstdAmt>
        <DrctDbtTx>
          <MndtRltdInf>
            <MndtId>A1</MndtId>
            <DtOfSgntr>2019-08-28</DtOfSgntr>
          </MndtRltdInf>
          <CdtrSchmeId>
            <Id>
              <PrvtId>
                <Othr>
                  <Id>foo</Id>
                  <SchmeNm>
                    <Prtry>SEPA</Prtry>
                  </SchmeNm>
                </Othr>
              </PrvtId>
            </Id>
          </CdtrSchmeId>
        </DrctDbtTx>
        <DbtrAgt>
          <FinInstnId>
            <BIC>TEST BIC</BIC>
          </FinInstnId>
        </DbtrAgt>
        <Dbtr>
          <Nm>foo</Nm>
        </Dbtr>
        <DbtrAcct>
          <Id>
            <IBAN>TEST IBAN</IBAN>
          </Id>
        </DbtrAcct>
        <RmtInf>
          <Ustrd>2, foo</Ustrd>
        </RmtInf>
      </DrctDbtTxInf>
    </PmtInf>
  </CstmrDrctDbtInitn>
</Document>

XML;

        $wildcards = [
            new StringWildcard('CstmrDrctDbtInitn.GrpHdr.MsgId'),
            new DateTimeWildcard('CstmrDrctDbtInitn.GrpHdr.CreDtTm', 'Y-m-d\TH:i:s'),
            new StringWildcard('CstmrDrctDbtInitn.PmtInf.PmtInfId'),
            new StringWildcard('CstmrDrctDbtInitn.PmtInf.DrctDbtTxInf[*].PmtId.EndToEndId'),
        ];

        $this->assertMatchesXmlSnapshot($data, $wildcards);
    }
}
