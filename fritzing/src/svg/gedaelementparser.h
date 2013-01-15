// This file was generated by qlalr - DO NOT EDIT!

/*******************************************************************

Part of the Fritzing project - http://fritzing.org
Copyright (c) 2007-08 Fachhochschule Potsdam - http://fh-potsdam.de

Fritzing is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Fritzing is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Fritzing.  If not, see <http://www.gnu.org/licenses/>.

********************************************************************

$Revision: 3848 $:
$Author: cohen@irascible.com $:
$Date: 2009-12-10 12:48:23 +0100 (Thu, 10 Dec 2009) $

********************************************************************/

#ifndef GEDAELEMENTPARSER_H
#define GEDAELEMENTPARSER_H

#include <QVariant>
#include <QVector>
#include "gedaelementgrammar_p.h"

class GedaElementLexer;

class GedaElementParser: public GedaElementGrammar
{
public:
    GedaElementParser();
    ~GedaElementParser();

    bool parse(GedaElementLexer *lexer);
    QVector<QVariant> & symStack();
    QString errorMessage() const;
    QVariant result() const;

private:
    void reallocateStack();
    int m_tos;
    QVector<int> m_stateStack;
    QVector<QVariant> m_symStack;
    QString m_errorMessage;
    QVariant m_result;
};

#endif // GEDAELEMENTPARSER_H
