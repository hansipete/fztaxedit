/*******************************************************************

Part of the Fritzing project - http://fritzing.org
Copyright (c) 2007-2010 Fachhochschule Potsdam - http://fh-potsdam.de

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

$Revision: 4542 $:
$Author: cohen@irascible.com $:
$Date: 2010-10-15 07:15:26 +0200 (Fri, 15 Oct 2010) $

********************************************************************/


#ifndef PROCESSEVENTBLOCKER_H_
#define PROCESSEVENTBLOCKER_H_


#include <QMutex>

class ProcessEventBlocker {

protected:
	ProcessEventBlocker();
	~ProcessEventBlocker();
	bool _isProcessing();
	void _processEvents();
	void _inc(int i);

public:
	static void processEvents();
	static bool isProcessing();
	static void block();
	static void unblock();

protected:
	int m_count;
	QMutex m_mutex;

protected:
	static ProcessEventBlocker * m_singleton;
};

#endif /* PROCESSEVENTBLOCKER_H_ */
